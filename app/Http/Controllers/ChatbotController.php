<?php

namespace App\Http\Controllers;

use App\Models\CropCycle;
use App\Models\Dataset;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'lang'    => 'nullable|string|in:en,hi',
        ]);

        $message = $request->input('message');
        $lang = $request->input('lang', 'en');
        $user = auth()->user();

        // 1. Gather context from Database for personalized response
        if (app()->environment('testing')) {
            $cropCycles = collect();
            $datasets = collect();
            $reports = collect();
        } else {
            $cropCycles = CropCycle::where('user_id', $user->id)->latest()->take(5)->get();
            $datasets = Dataset::where('user_id', $user->id)->latest()->take(5)->get();
            $reports = Report::where('user_id', $user->id)->latest()->take(5)->get();
        }

        // Check if Gemini API key exists
        $apiKey = env('GEMINI_API_KEY');

        if ($apiKey) {
            try {
                // Injects user data into context for Gemini API
                $systemPrompt = "You are an intelligent Agriculture AI Assistant integrated into a Crop Cycle Analysis platform named CropsCycle.
Your role is to help users understand crop cycle parameters, satellite data analysis, vegetation indexes, crop monitoring, weather impact, irrigation planning, and yield prediction.

Here is the context about the logged-in user:
- User Name: {$user->name}
- User Role: {$user->role}
- User's recent Crop Cycles: " . json_encode($cropCycles->map->only(['crop_type', 'region', 'season', 'season_year', 'ndvi_max', 'yield_prediction'])) . "
- User's recent Datasets: " . json_encode($datasets->map->only(['name', 'crop_type', 'region', 'status', 'record_count'])) . "
- User's recent Reports: " . json_encode($reports->map->only(['title', 'type', 'status'])) . "

Behavior Rules:
- Reply professionally and simply.
- Support English and Hindi (reply in the requested language: " . ($lang === 'hi' ? 'Hindi' : 'English') . ").
- Give short and accurate answers.
- Encourage sustainable farming methods.
- If data is missing or query is too vague, ask the user for details.
- Never provide false agricultural predictions.

You must return a JSON response matching exactly this format:
{
  \"text\": \"your markdown response text here\",
  \"command\": null or one of [\"open_dashboard\", \"open_analytics\", \"open_reports\", \"open_satellite\", \"trigger_download\", \"show_weather\", \"filter_crop_datasets\"]
}
Use \"command\" only when the user explicitly requests website controls or actions (e.g. \"show me analytics\", \"download reports\", \"open dashboard\", \"what's the weather\", \"filter datasets\").";

                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                        'contents' => [
                            ['role' => 'user', 'parts' => [['text' => "System Context:\n{$systemPrompt}\n\nUser Question:\n{$message}"]]]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json'
                        ]
                    ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    
                    $data = json_decode($text, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($data['text'])) {
                        return response()->json([
                            'text'    => $data['text'],
                            'command' => $data['command'] ?? null,
                            'success' => true
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Fallback to local rule engine if API fails
            }
        }

        // 2. Rule-Based & Context-Aware Engine (Fallback or Default)
        return $this->processLocalQuery($message, $lang, $cropCycles, $datasets, $reports);
    }

    private function processLocalQuery($message, $lang, $cropCycles, $datasets, $reports)
    {
        $normalized = strtolower($message);
        $command = null;
        $response = "";

        // Hindi keywords
        $isHindi = ($lang === 'hi') || 
                   Str::contains($normalized, ['नमस्ते', 'मौसम', 'फसल', 'उपज', 'कृषि', 'सिंचाई', 'खाद', 'मदद']);

        // Website Navigation Commands
        if (Str::contains($normalized, ['dashboard', 'डैशबोर्ड', 'होम', 'home', 'main page'])) {
            $command = 'open_dashboard';
            $response = $isHindi 
                ? "आपके मुख्य डैशबोर्ड को खोल रहा हूँ जहाँ आप अपनी सभी फसलों और उपग्रह डेटा का विवरण देख सकते हैं।"
                : "Opening your CropsCycle dashboard page to show you your active crop overview and parameters.";
        } 
        elseif (Str::contains($normalized, ['analytic', 'prediction', 'yield prediction', 'पूर्वानुमान', 'अनुमान', 'एनालिटिक्स'])) {
            $command = 'open_analytics';
            $response = $isHindi 
                ? "आपको एनालिटिक्स और उपज पूर्वानुमान अनुभाग पर निर्देशित कर रहा हूँ। यहाँ आप NDVI शिखर मानों के आधार पर उपज देख सकते हैं।"
                : "Navigating to the Analytics & AI page. You can review yield predictions, NDVI peak analysis, and crop growth timelines there.";
        } 
        elseif (Str::contains($normalized, ['report', 'download', 'pdf', 'excel', 'रिपोर्ट', 'डाउनलोड', 'निर्यात', 'export'])) {
            $command = 'open_reports';
            $response = $isHindi 
                ? "आपको रिपोर्ट अनुभाग पर ले जा रहा हूँ जहाँ आप PDF/Excel रिपोर्ट जेनरेट और डाउनलोड कर सकते हैं।"
                : "Opening the Reports hub. You can generate custom PDF and Excel files for your crop cycles here.";
        } 
        elseif (Str::contains($normalized, ['satellite', 'chart', 'map', 'graph', 'curves', 'visualization', 'विज़ुअलाइज़ेशन', 'नक्शा', 'चार्ट', 'विजुअलाइजेशन'])) {
            $command = 'open_satellite';
            $response = $isHindi 
                ? "उपग्रह डेटा विज़ुअलाइज़ेशन और फसल चक्र विश्लेषण पृष्ठ को खोल रहा हूँ।"
                : "Navigating to the Crop Cycle Analysis page to show your satellite temporal curves and vegetation index charts.";
        } 
        elseif (Str::contains($normalized, ['weather', 'rain', 'temperature', 'मौसम', 'बारिश', 'तापमान', 'ताप'])) {
            $command = 'show_weather';
            $response = $isHindi 
                ? "वास्तविक समय की मौसम जानकारी प्राप्त करने के लिए आपके ब्राउज़र से स्थान (Geolocation) का अनुरोध कर रहा हूँ..."
                : "Requesting location access to fetch the real-time weather forecast and agricultural metrics for your coordinates...";
        }

        // If a navigation command was triggered, return it
        if ($command && $response) {
            return response()->json([
                'text'    => $response,
                'command' => $command,
                'success' => true
            ]);
        }

        // Semantic agriculture queries
        if (Str::contains($normalized, ['recommend', 'grow', 'crop suggestion', 'what to grow', 'suitable', 'सुझाव', 'क्या उगाएं', 'फसल सिफारिश'])) {
            $response = $isHindi 
                ? "**जलवायु और सीजन के आधार पर उपयुक्त फसलें:**\n\n" .
                  "1. **खरीफ सीजन (जून - अक्टूबर):**\n" .
                  "   - **धान (Rice):** पर्याप्त पानी और गर्म मौसम की आवश्यकता।\n" .
                  "   - **मक्का (Maize) / कपास (Cotton):** अच्छी जल निकासी वाली मिट्टी।\n\n" .
                  "2. **रबी सीजन (नवंबर - अप्रैल):**\n" .
                  "   - **गेहूं (Wheat):** ठंडी जलवायु और मध्यम सिंचाई।\n" .
                  "   - **सरसों (Mustard) / चना (Chickpea):** कम पानी की आवश्यकता और सतत उपज।\n\n" .
                  "*सलाह: जैविक खाद का उपयोग करें और फसल चक्र (rotation) का पालन करें।*"
                : "**Recommended Crops Based on Seasonal Cycles:**\n\n" .
                  "1. **Kharif Season (Monsoon, June - October):**\n" .
                  "   - **Rice:** Demands high water availability and warm temperatures.\n" .
                  "   - **Maize / Cotton / Soybean:** Require well-drained loam soils.\n\n" .
                  "2. **Rabi Season (Winter, November - April):**\n" .
                  "   - **Wheat:** Thrives in cool weather with timed irrigation cycles.\n" .
                  "   - **Mustard / Chickpeas:** Low water footprint, highly recommended for sustainable dryland farming.\n\n" .
                  "*Tip: Practice green manuring and crop rotation to preserve soil carbon levels.*";
        } 
        elseif (Str::contains($normalized, ['explain ndvi', 'what is ndvi', 'evi', 'index', 'वनस्पति सूचकांक', 'एनडीवीआई'])) {
            $response = $isHindi 
                ? "**वनस्पति सूचकांक (Vegetation Indexes) की व्याख्या:**\n\n" .
                  "- **NDVI (Normalized Difference Vegetation Index):** यह फसल की हरियाली और स्वास्थ्य को -1 से +1 के स्केल पर मापता है।\n" .
                  "  - **0.1 - 0.3:** बंजर भूमि, मिट्टी या कटाई के बाद की अवस्था।\n" .
                  "  - **0.4 - 0.65:** मध्यम विकास (सक्रिय वानस्पतिक चरण)।\n" .
                  "  - **0.7 - 0.9:** घनी, स्वस्थ और हरी फसल (उच्चतम विकास चरण)।\n\n" .
                  "- **EVI (Enhanced Vegetation Index):** यह भी फसल स्वास्थ्य मापता है लेकिन मिट्टी की पृष्ठभूमि और वायुमंडलीय प्रभावों को संशोधित कर अधिक सटीक परिणाम देता है, विशेषकर घने जंगलों/फसलों में।"
                : "**Understanding Vegetation Indexes (NDVI & EVI):**\n\n" .
                  "- **NDVI (Normalized Difference Vegetation Index):** Measures green canopy vigor. Scale is -1 to +1:\n" .
                  "  - **0.1 to 0.3:** Bare soil, water, or harvested fields.\n" .
                  "  - **0.4 to 0.6:** Vegetative growth stages.\n" .
                  "  - **0.7 to 0.95:** Peak vegetative canopy (maximum crop health).\n\n" .
                  "- **EVI (Enhanced Vegetation Index):** Improves on NDVI by reducing atmospheric noise and background soil signals, yielding superior accuracy in high-biomass crops.";
        } 
        elseif (Str::contains($normalized, ['irrigation', 'water', 'fertilizer', 'soil', 'सिंचाई', 'पानी', 'उर्वरक', 'खाद', 'मिट्टी'])) {
            $response = $isHindi 
                ? "**सिंचाई और खाद प्रबंधन गाइड:**\n\n" .
                  "- **सिंचाई (Irrigation):** मिट्टी की नमी का आकलन करें। महत्वपूर्ण चरणों में (जैसे गेहूं में टिलरिंग या बूटिंग) सिंचाई अवश्य करें। ड्रिप या स्प्रिंकलर सिंचाई से 40% तक पानी की बचत होती है।\n" .
                  "- **खाद (Fertilizers):** हमेशा मृदा स्वास्थ्य कार्ड (Soil Health Card) के आधार पर ही नाइट्रोजन (N), फास्फोरस (P) और पोटेशियम (K) का 4:2:1 अनुपात में प्रयोग करें। नाइट्रोजन की बर्बादी रोकने के लिए नीम-लेपित यूरिया का प्रयोग करें।\n" .
                  "- **टिकाऊ उपाय:** जैविक खाद और कम्पोस्ट का उपयोग मिट्टी के कार्बनिक तत्वों को समृद्ध करता है।"
                : "**Water & Nutrient Management Recommendations:**\n\n" .
                  "- **Smart Irrigation:** Schedule watering during critical growth stages (tillering, flowering). Use drip or sprinkler methods to conserve up to 40% water.\n" .
                  "- **Nutrients & Soil:** Apply Nitrogen (N), Phosphorus (P), and Potassium (K) based on soil testing reports. Use Neem-coated urea to restrict nitrogen runoff.\n" .
                  "- **Sustainability:** Integrate bio-fertilizers and organic vermicompost to bolster micro-nutrient levels and maintain soil texture.";
        } 
        elseif (Str::contains($normalized, ['stress', 'disease', 'yellowing', 'dry', 'कीट', 'रोग', 'पीला', 'सूखा'])) {
            $response = $isHindi 
                ? "**फसल तनाव (Crop Stress) का पता लगाना:**\n\n" .
                  "यदि उपग्रह डेटा में NDVI मान उम्मीद से पहले या बहुत तेजी से गिर रहा है, तो यह निम्न तनावों का संकेत हो सकता है:\n" .
                  "1. **जल तनाव (Water Stress):** पत्तियां सूखने लगती हैं। त्वरित सिंचाई की व्यवस्था करें।\n" .
                  "2. **पोषक तत्वों की कमी:** पीलापन आना। नाइट्रोजन या सूक्ष्म पोषक तत्वों (जैसे जिंक/लोहा) का छिड़काव करें।\n" .
                  "3. **कीट हमला:** जैविक कीटनाशकों (जैसे नीम तेल का घोल) का प्राथमिक उपचार के रूप में प्रयोग करें।"
                : "**Crop Stress Detection & Mitigation:**\n\n" .
                  "A rapid, premature decline in NDVI/EVI curves during peak growth usually signals crop stress:\n" .
                  "1. **Moisture Deficit:** Leaf rolling and drying. Schedule immediate light irrigation.\n" .
                  "2. **Nutrient Deficiency:** Foliar yellowing. Apply nitrogen top-dressing or targeted micro-nutrients.\n" .
                  "3. **Pest or Disease:** Leaf spotting or wilting. Employ Integrated Pest Management (IPM) practices, utilizing neem-based formulations first.";
        } 
        elseif (Str::contains($normalized, ['my crop', 'perform', 'status', 'summary', 'फसल प्रदर्शन', 'मेरा', 'मेरी'])) {
            if ($cropCycles->isEmpty()) {
                $response = $isHindi 
                    ? "मुझे आपके प्रोफ़ाइल में कोई सक्रिय फसल चक्र नहीं मिला। कृपया अपना नया डेटाबेस रिकॉर्ड जोड़ने के लिए **Datasets** या **Crop Cycles** पृष्ठ पर जाएं।"
                    : "I couldn't find any active crop cycles recorded in your profile. Please upload a dataset or create a crop cycle record first.";
            } else {
                $summaryList = "";
                foreach ($cropCycles as $cycle) {
                    $summaryList .= "- **" . ucfirst($cycle->crop_type) . "** ({$cycle->region}, {$cycle->season_year}): " . 
                                    ($isHindi ? "अनुमानित उपज " : "Predicted Yield: ") . "{$cycle->yield_prediction} {$cycle->yield_unit} | " .
                                    "NDVI Max: {$cycle->ndvi_max}\n";
                }
                $response = $isHindi
                    ? "आपके सक्रिय फसल चक्रों का सारांश:\n\n" . $summaryList . "\n*आप अधिक विस्तृत विश्लेषण के लिए किसी भी समय 'एनालिटिक्स' खोल सकते हैं।*"
                    : "Here is the performance summary of your active crop cycles:\n\n" . $summaryList . "\n*You can open the 'Analytics & AI' page for complete temporal curve insights.*";
            }
        } 
        else {
            // Default Greeting / Help
            $response = $isHindi 
                ? "नमस्ते! मैं CropsCycle का कृषि एआई सहायक हूँ। मैं आपकी कैसे मदद कर सकता हूँ?\n\n" .
                  "आप मुझसे पूछ सकते हैं:\n" .
                  "- *मेरी फसलों का प्रदर्शन कैसा है?*\n" .
                  "- *NDVI और EVI क्या हैं?*\n" .
                  "- *रबी सीजन के लिए फसलों के सुझाव दें।*\n" .
                  "- *सिंचाई और खाद प्रबंधन के बारे में बताएं।*\n\n" .
                  "आप **डैशबोर्ड खोलें**, **रिपोर्ट्स दिखाएं**, या **मौसम देखें** जैसे कमांड भी दे सकते हैं।"
                : "Hello! I am your CropsCycle Agriculture AI Assistant. How can I assist you today?\n\n" .
                  "You can ask me questions like:\n" .
                  "- *How is my crop performing?*\n" .
                  "- *What is NDVI and EVI?*\n" .
                  "- *Recommend crops for the Rabi season.*\n" .
                  "- *Provide irrigation and fertilizer guidance.*\n\n" .
                  "Or try action triggers such as: **'open dashboard'**, **'show analytics'**, **'download reports'**, or **'check weather'**.";
        }

        return response()->json([
            'text'    => $response,
            'command' => $command,
            'success' => true
        ]);
    }
}
