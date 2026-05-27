<div x-data="chatbotComponent()" x-init="init()" class="chatbot-container">
    {{-- ── Chat Trigger Button ───────────────────────────────────────────────── --}}
    <button @click="toggleOpen()" class="chatbot-trigger" :class="{ 'active': open }" aria-label="Toggle Agriculture Assistant">
        <div class="trigger-pulse"></div>
        <i class="bi bi-chat-fill chat-icon" x-show="!open"></i>
        <i class="bi bi-x-lg close-icon" x-show="open"></i>
        <span class="trigger-badge" x-show="unreadCount > 0" x-text="unreadCount"></span>
    </button>

    {{-- ── Chat Window ───────────────────────────────────────────────────────── --}}
    <div x-show="open" 
         x-transition:enter="transition-all ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition-all ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
         class="chatbot-window"
         style="display: none;">
        
        {{-- Header --}}
        <header class="chatbot-header">
            <div class="bot-info">
                <span class="bot-avatar">
                    <i class="bi bi-robot"></i>
                    <span class="online-indicator"></span>
                </span>
                <div>
                    <h3 class="bot-name" x-text="lang === 'hi' ? 'कृषि एआई सहायक' : 'Agriculture AI Assistant'"></h3>
                    <span class="bot-status" x-text="lang === 'hi' ? 'सक्रिय' : 'Online & Ready'"></span>
                </div>
            </div>
            
            <div class="header-actions">
                {{-- Language Selector --}}
                <button @click="toggleLang()" class="header-action-btn" :title="lang === 'hi' ? 'Change to English' : 'हिंदी में बदलें'">
                    <i class="bi bi-translate"></i>
                    <span class="lang-label" x-text="lang === 'hi' ? 'EN' : 'HI'"></span>
                </button>
                
                {{-- PDF Export --}}
                <button @click="downloadPdf()" class="header-action-btn" :title="lang === 'hi' ? 'चैट इतिहास डाउनलोड करें' : 'Download Transcript as PDF'">
                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                </button>
                
                {{-- Close Button --}}
                <button @click="toggleOpen()" class="header-action-btn close-btn">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
        </header>

        {{-- Messages Body --}}
        <div class="chatbot-messages" x-ref="messageContainer">
            {{-- Welcome Message --}}
            <div class="chat-message bot-msg">
                <div class="msg-bubble">
                    <p x-text="lang === 'hi' ? 'नमस्ते! मैं CropsCycle का कृषि एआई सहायक हूँ। मैं आपकी कैसे मदद कर सकता हूँ?' : 'Hello! I am your CropsCycle Agriculture AI Assistant. How can I assist you today?'"></p>
                    <div class="msg-time" x-text="getFormattedTime()"></div>
                </div>
            </div>

            {{-- Message History Loop --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div class="chat-message" :class="msg.sender === 'user' ? 'user-msg' : 'bot-msg'">
                    <div class="msg-bubble" :class="{ 'loading-msg': msg.loading }">
                        {{-- Loading spinner --}}
                        <div x-show="msg.loading" class="dot-loader">
                            <span></span><span></span><span></span>
                        </div>

                        {{-- Text content with custom markdown rendering --}}
                        <div x-show="!msg.loading" x-html="renderMarkdown(msg.text)"></div>

                        {{-- Real time weather card (if message is weather content) --}}
                        <div x-show="msg.isWeather && msg.weatherData" class="weather-report-card">
                            <div class="weather-header">
                                <i class="bi bi-cloud-sun-fill weather-icon-large"></i>
                                <div>
                                    <h4 x-text="msg.weatherData.location"></h4>
                                    <p x-text="msg.weatherData.description"></p>
                                </div>
                            </div>
                            <div class="weather-metrics">
                                <div class="metric-item">
                                    <i class="bi bi-thermometer-half"></i>
                                    <span x-text="msg.weatherData.temp + '°C'"></span>
                                    <label x-text="lang === 'hi' ? 'तापमान' : 'Temp'"></label>
                                </div>
                                <div class="metric-item">
                                    <i class="bi bi-moisture"></i>
                                    <span x-text="msg.weatherData.humidity + '%'"></span>
                                    <label x-text="lang === 'hi' ? 'आर्द्रता' : 'Humidity'"></label>
                                </div>
                                <div class="metric-item">
                                    <i class="bi bi-cloud-rain-heavy"></i>
                                    <span x-text="msg.weatherData.rainProb + '%'"></span>
                                    <label x-text="lang === 'hi' ? 'बारिश' : 'Rain Prob'"></label>
                                </div>
                            </div>
                            <div class="weather-footer">
                                <i class="bi bi-info-circle-fill"></i>
                                <span x-text="msg.weatherData.suggestion"></span>
                            </div>
                        </div>

                        {{-- Text to speech trigger (for bot answers) --}}
                        <button x-show="!msg.loading && msg.sender === 'bot'" @click="speak(msg.text)" class="speak-btn" :title="lang === 'hi' ? 'सुने' : 'Listen'">
                            <i class="bi bi-volume-up-fill"></i>
                        </button>
                        
                        <div class="msg-time" x-text="msg.time"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Suggestion Chips --}}
        <div class="chatbot-suggestions" x-show="messages.length <= 4">
            <button @click="sendSuggestion(lang === 'hi' ? 'फसल सुझाव दें' : 'Recommend suitable crops')" class="suggestion-chip">
                <i class="bi bi-flower1 text-success"></i>
                <span x-text="lang === 'hi' ? 'फसल सुझाव' : 'Crop Suggestions'"></span>
            </button>
            <button @click="sendSuggestion(lang === 'hi' ? 'NDVI क्या है' : 'Explain NDVI vs EVI')" class="suggestion-chip">
                <i class="bi bi-graph-up-arrow text-primary"></i>
                <span x-text="lang === 'hi' ? 'NDVI की व्याख्या' : 'Explain NDVI'"></span>
            </button>
            <button @click="sendSuggestion(lang === 'hi' ? 'मौसम कैसा है' : 'What is the current weather?')" class="suggestion-chip">
                <i class="bi bi-cloud-sun-fill text-warning"></i>
                <span x-text="lang === 'hi' ? 'मौसम विवरण' : 'Check Weather'"></span>
            </button>
            <button @click="sendSuggestion(lang === 'hi' ? 'मेरा फसल प्रदर्शन' : 'Show my crop performance')" class="suggestion-chip">
                <i class="bi bi-speedometer2 text-info"></i>
                <span x-text="lang === 'hi' ? 'फसल प्रदर्शन' : 'Crop Performance'"></span>
            </button>
        </div>

        {{-- Input Footer --}}
        <footer class="chatbot-input-footer">
            {{-- Voice input button --}}
            <button @click="toggleListening()" class="mic-btn" :class="{ 'recording': isListening }" :title="lang === 'hi' ? 'बोलकर टाइप करें' : 'Voice Input'">
                <i class="bi" :class="isListening ? 'bi-mic-mute-fill' : 'bi-mic-fill'"></i>
            </button>

            <form @submit.prevent="submitMessage()" class="input-form">
                <input x-model="userInput" 
                       type="text" 
                       class="chat-input" 
                       :placeholder="lang === 'hi' ? 'कृषि प्रश्न पूछें...' : 'Ask agricultural questions...'" 
                       :disabled="loading">
                <button type="submit" class="send-btn" :disabled="!userInput.trim() || loading" aria-label="Send">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </footer>
    </div>
</div>

<style>
/* ─── Chatbot Widget Styling ──────────────────────────────────────────────── */
.chatbot-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1100;
    font-family: var(--font-sans);
}

/* Floating trigger button */
.chatbot-trigger {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--brand-green), #22c55e);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(22, 163, 74, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s;
}
.chatbot-trigger:hover {
    transform: scale(1.08);
    box-shadow: 0 12px 30px rgba(22, 163, 74, 0.55);
}
.chatbot-trigger.active {
    transform: rotate(90deg);
    background: linear-gradient(135deg, var(--brand-rose), #f43f5e);
    box-shadow: 0 10px 25px rgba(225, 29, 72, 0.4);
}
.chatbot-trigger i {
    font-size: 1.5rem;
    transition: opacity 0.2s;
}

/* Pulse animation ring */
.trigger-pulse {
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    border: 2px dashed rgba(34, 197, 94, 0.4);
    animation: spin 8s linear infinite;
    pointer-events: none;
}
.chatbot-trigger.active .trigger-pulse {
    border-color: rgba(244, 63, 94, 0.4);
}

.trigger-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 20px;
    height: 20px;
    background: var(--brand-rose);
    border-radius: 50%;
    font-size: 0.7rem;
    font-weight: 700;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--bg-primary);
}

/* Chat Window Layout */
.chatbot-window {
    position: fixed;
    bottom: 96px;
    right: 24px;
    width: 400px;
    height: 550px;
    max-height: 80vh;
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@media (max-width: 500px) {
    .chatbot-window {
        width: calc(100vw - 32px);
        right: 16px;
        left: 16px;
        bottom: 90px;
    }
}

/* Header */
.chatbot-header {
    padding: 16px 20px;
    background: linear-gradient(135deg, rgba(22, 163, 74, 0.08), rgba(37, 99, 235, 0.05));
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.bot-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.bot-avatar {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, var(--brand-green), #10b981);
    color: white;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    position: relative;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
}
.online-indicator {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 10px;
    height: 10px;
    background: #22c55e;
    border-radius: 50%;
    border: 2px solid var(--bg-card);
}
.bot-name {
    margin: 0;
    font-family: var(--font-heading);
    font-size: 0.95rem;
    font-weight: 700;
}
.bot-status {
    font-size: 0.75rem;
    color: var(--text-muted);
    display: block;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}
.header-action-btn {
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 6px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: background var(--transition-fast), color var(--transition-fast);
}
.header-action-btn:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}
.lang-label {
    font-size: 0.72rem;
    font-weight: 700;
}
.close-btn {
    font-size: 1rem;
}

/* Messages Body */
.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    scrollbar-width: thin;
}
.chatbot-messages::-webkit-scrollbar {
    width: 6px;
}
.chatbot-messages::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 99px;
}

.chat-message {
    display: flex;
    max-width: 85%;
}
.chat-message.bot-msg {
    align-self: flex-start;
}
.chat-message.user-msg {
    align-self: flex-end;
}

.msg-bubble {
    padding: 12px 16px;
    border-radius: 20px;
    font-size: 0.88rem;
    line-height: 1.5;
    position: relative;
    display: inline-block;
    word-break: break-word;
}
.bot-msg .msg-bubble {
    background: var(--bg-hover);
    color: var(--text-primary);
    border-top-left-radius: 4px;
    border: 1px solid var(--border-color);
}
.user-msg .msg-bubble {
    background: linear-gradient(135deg, var(--brand-green), #15803d);
    color: white;
    border-top-right-radius: 4px;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
}

.msg-bubble p {
    margin: 0 0 8px;
}
.msg-bubble p:last-child {
    margin-bottom: 0;
}
.msg-bubble ul, .msg-bubble ol {
    margin: 8px 0;
    padding-left: 20px;
}
.msg-bubble li {
    margin-bottom: 4px;
}

.msg-time {
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-top: 4px;
    text-align: right;
}
.user-msg .msg-time {
    color: rgba(255, 255, 255, 0.7);
}

.speak-btn {
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--text-muted);
    padding: 2px 4px;
    border-radius: 4px;
    position: absolute;
    right: 8px;
    top: 4px;
    font-size: 0.82rem;
    opacity: 0;
    transition: opacity 0.2s, color 0.2s;
}
.msg-bubble:hover .speak-btn {
    opacity: 1;
}
.speak-btn:hover {
    color: var(--brand-green);
}

/* Suggestion Chips */
.chatbot-suggestions {
    padding: 8px 16px;
    display: flex;
    gap: 8px;
    overflow-x: auto;
    white-space: nowrap;
    scrollbar-width: none;
    border-top: 1px solid var(--border-color);
    background: rgba(0, 0, 0, 0.01);
}
.chatbot-suggestions::-webkit-scrollbar {
    display: none;
}
.suggestion-chip {
    padding: 6px 12px;
    border: 1px solid var(--border-color);
    background: var(--bg-card);
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-secondary);
    transition: background 0.2s, border-color 0.2s, transform 0.1s;
}
.suggestion-chip:hover {
    background: var(--bg-hover);
    border-color: rgba(22, 163, 74, 0.24);
    transform: translateY(-1px);
}
.suggestion-chip:active {
    transform: translateY(0);
}

/* Input Footer */
.chatbot-input-footer {
    padding: 12px 16px;
    border-top: 1px solid var(--border-color);
    background: var(--bg-card);
    display: flex;
    align-items: center;
    gap: 8px;
}
.input-form {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chat-input {
    flex: 1;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px 14px;
    background: var(--bg-input);
    color: var(--text-primary);
    outline: none;
    font-size: 0.86rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.chat-input:focus {
    border-color: var(--brand-green);
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.08);
}
.send-btn {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--brand-green), #16a34a);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform var(--transition-fast);
}
.send-btn:hover:not(:disabled) {
    transform: translateY(-1px);
}
.send-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.mic-btn {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-color);
    background: var(--bg-card);
    color: var(--text-secondary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.mic-btn:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}
.mic-btn.recording {
    background: rgba(225, 29, 72, 0.1);
    color: var(--brand-rose);
    border-color: var(--brand-rose);
    animation: recordingPulse 1.2s infinite;
}

/* Animations */
@keyframes spin {
    to { transform: rotate(360deg); }
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(16px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes recordingPulse {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.4); }
    70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(225, 29, 72, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(225, 29, 72, 0); }
}

/* Loading Dots */
.dot-loader {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
}
.dot-loader span {
    width: 8px;
    height: 8px;
    background: var(--text-muted);
    border-radius: 50%;
    animation: loaderDots 1.4s infinite ease-in-out both;
}
.dot-loader span:nth-child(1) { animation-delay: -0.32s; }
.dot-loader span:nth-child(2) { animation-delay: -0.16s; }
@keyframes loaderDots {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

/* Weather Report Card */
.weather-report-card {
    background: rgba(37, 99, 235, 0.05);
    border: 1px solid rgba(37, 99, 235, 0.15);
    border-radius: 16px;
    padding: 12px;
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
[data-theme="dark"] .weather-report-card {
    background: rgba(59, 130, 246, 0.08);
}
.weather-header {
    display: flex;
    align-items: center;
    gap: 10px;
}
.weather-icon-large {
    font-size: 1.8rem;
    color: var(--brand-amber);
}
.weather-header h4 {
    margin: 0;
    font-size: 0.88rem;
    font-weight: 700;
}
.weather-header p {
    margin: 2px 0 0;
    font-size: 0.78rem;
    color: var(--text-muted);
}
.weather-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    text-align: center;
}
.metric-item {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 8px 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.metric-item i {
    font-size: 0.98rem;
    color: var(--brand-green);
}
.metric-item span {
    font-weight: 700;
    font-size: 0.82rem;
}
.metric-item label {
    font-size: 0.68rem;
    color: var(--text-muted);
}
.weather-footer {
    display: flex;
    gap: 6px;
    font-size: 0.76rem;
    line-height: 1.4;
    color: var(--text-secondary);
}
.weather-footer i {
    color: var(--brand-blue);
}
</style>

<script>
function chatbotComponent() {
    return {
        open: false,
        lang: 'en',
        userInput: '',
        unreadCount: 1, // Start with 1 to prompt user
        isListening: false,
        loading: false,
        messages: [],
        recognition: null,
        speechSynth: window.speechSynthesis,
        voices: [],

        init() {
            const userId = "{{ auth()->id() }}";

            // Load language preference
            const savedLang = localStorage.getItem('chatbot-lang-' + userId);
            if (savedLang) this.lang = savedLang;

            // Load chat history from localStorage
            const savedHistory = localStorage.getItem('chatbot-history-' + userId);
            if (savedHistory) {
                this.messages = JSON.parse(savedHistory);
                this.unreadCount = 0;
            }

            // Load open state preference
            const savedOpen = localStorage.getItem('chatbot-open-' + userId);
            if (savedOpen === 'true') {
                this.open = true;
                this.unreadCount = 0;
                this.$nextTick(() => this.scrollToBottom());
            }

            // Initialize Speech Recognition
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (SpeechRecognition) {
                this.recognition = new SpeechRecognition();
                this.recognition.continuous = false;
                this.recognition.interimResults = false;
                this.recognition.lang = this.lang === 'hi' ? 'hi-IN' : 'en-US';

                this.recognition.onresult = (event) => {
                    const resultText = event.results[0][0].transcript;
                    this.userInput = resultText;
                    this.isListening = false;
                };

                this.recognition.onerror = (event) => {
                    console.error('Speech recognition error:', event.error);
                    this.isListening = false;
                };

                this.recognition.onend = () => {
                    this.isListening = false;
                };
            }

            // Initialize speech voices
            if (this.speechSynth) {
                this.voices = this.speechSynth.getVoices();
                if (speechSynthesis.onvoiceschanged !== undefined) {
                    speechSynthesis.onvoiceschanged = () => {
                        this.voices = this.speechSynth.getVoices();
                    };
                }
            }

            // Clear chatbot local storage upon logout to prevent data persistence across sessions
            const clearChatbotHistory = () => {
                for (let i = localStorage.length - 1; i >= 0; i--) {
                    const key = localStorage.key(i);
                    if (key && key.startsWith('chatbot-')) {
                        localStorage.removeItem(key);
                    }
                }
            };

            // Intercept standard form submissions for logout
            window.addEventListener('submit', (e) => {
                if (e.target && e.target.action && e.target.action.includes('logout')) {
                    clearChatbotHistory();
                }
            });

            // Intercept clicks on logout links or buttons
            window.addEventListener('click', (e) => {
                const target = e.target.closest('a, button, [role="button"]');
                if (target) {
                    const href = target.getAttribute('href') || '';
                    const action = target.closest('form')?.getAttribute('action') || '';
                    const text = (target.textContent || '').toLowerCase();
                    if (href.includes('logout') || action.includes('logout') || text.includes('log out') || text.includes('logout')) {
                        clearChatbotHistory();
                    }
                }
            });
        },

        toggleOpen() {
            this.open = !this.open;
            localStorage.setItem('chatbot-open-' + "{{ auth()->id() }}", this.open);
            if (this.open) {
                this.unreadCount = 0;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        toggleLang() {
            this.lang = this.lang === 'hi' ? 'en' : 'hi';
            localStorage.setItem('chatbot-lang-' + "{{ auth()->id() }}", this.lang);
            if (this.recognition) {
                this.recognition.lang = this.lang === 'hi' ? 'hi-IN' : 'en-US';
            }
        },

        getFormattedTime() {
            const now = new Date();
            return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        scrollToBottom() {
            const container = this.$refs.messageContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        toggleListening() {
            if (!this.recognition) {
                alert(this.lang === 'hi' ? 'आपका ब्राउज़र वॉयस इनपुट का समर्थन नहीं करता है।' : 'Your browser does not support Speech Recognition.');
                return;
            }

            if (this.isListening) {
                this.recognition.stop();
            } else {
                this.isListening = true;
                this.recognition.start();
            }
        },

        speak(text) {
            if (!this.speechSynth) return;

            // Stop any current reading
            this.speechSynth.cancel();

            // Strip markdown tags from reading
            const cleanText = text.replace(/[*#_`~-]/g, '');

            const utterance = new SpeechSynthesisUtterance(cleanText);
            utterance.lang = this.lang === 'hi' ? 'hi-IN' : 'en-US';

            // Find matching voice
            const targetVoice = this.voices.find(voice => voice.lang.startsWith(this.lang));
            if (targetVoice) utterance.voice = targetVoice;

            this.speechSynth.speak(utterance);
        },

        sendSuggestion(text) {
            this.userInput = text;
            this.submitMessage();
        },

        submitMessage() {
            const text = this.userInput.trim();
            if (!text || this.loading) return;

            this.userInput = '';
            
            // Add user message to history
            this.messages.push({
                sender: 'user',
                text: text,
                time: this.getFormattedTime()
            });

            this.saveHistory();

            this.$nextTick(() => this.scrollToBottom());
            
            // Show loading message
            const loadingMsgIndex = this.messages.push({
                sender: 'bot',
                text: '',
                time: this.getFormattedTime(),
                loading: true
            }) - 1;

            this.loading = true;

            // Fetch response from Backend API
            fetch("{{ route('chatbot.message') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: text,
                    lang: this.lang
                })
            })
            .then(res => res.json())
            .then(data => {
                this.messages.splice(loadingMsgIndex, 1); // remove loading placeholder
                
                if (data.success) {
                    this.messages.push({
                        sender: 'bot',
                        text: data.text,
                        time: this.getFormattedTime()
                    });

                    // Execute command if triggered by AI
                    if (data.command) {
                        this.executeCommand(data.command);
                    }
                } else {
                    this.messages.push({
                        sender: 'bot',
                        text: this.lang === 'hi' ? 'क्षमा करें, संदेश संसाधित करने में समस्या हुई।' : 'Sorry, I encountered an issue processing your request.',
                        time: this.getFormattedTime()
                    });
                }
                this.saveHistory();
                this.$nextTick(() => this.scrollToBottom());
            })
            .catch(err => {
                console.error(err);
                this.messages.splice(loadingMsgIndex, 1);
                this.messages.push({
                    sender: 'bot',
                    text: this.lang === 'hi' ? 'कनेक्शन त्रुटि। कृपया बाद में प्रयास करें।' : 'Connection error. Please try again later.',
                    time: this.getFormattedTime()
                });
                this.$nextTick(() => this.scrollToBottom());
            })
            .finally(() => {
                this.loading = false;
            });
        },

        executeCommand(command) {
            // Give user a moment to read the redirect confirmation before executing
            setTimeout(() => {
                if (command === 'open_dashboard') {
                    window.location.href = "{{ route('dashboard') }}";
                } else if (command === 'open_analytics') {
                    window.location.href = "{{ route('analytics.index') }}";
                } else if (command === 'open_reports') {
                    window.location.href = "{{ route('reports.index') }}";
                } else if (command === 'open_satellite') {
                    window.location.href = "{{ route('crop-cycles.index') }}";
                } else if (command === 'trigger_download') {
                    window.location.href = "{{ route('reports.export.pdf') }}";
                } else if (command === 'show_weather') {
                    this.fetchWeather();
                }
            }, 1200);
        },

        fetchWeather() {
            if (!navigator.geolocation) {
                this.appendWeatherCard(null, this.lang === 'hi' ? 'जियोलोकेशन समर्थित नहीं है।' : 'Geolocation not supported.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,weather_code,precipitation_probability`)
                        .then(res => res.json())
                        .then(data => {
                            const cur = data.current;
                            const weatherCode = cur.weather_code;
                            
                            // Map codes to descriptions
                            let desc = this.lang === 'hi' ? 'साफ़ मौसम' : 'Clear Sky';
                            if (weatherCode > 0 && weatherCode <= 3) desc = this.lang === 'hi' ? 'आंशिक बादल' : 'Partly Cloudy';
                            else if (weatherCode >= 45 && weatherCode <= 48) desc = this.lang === 'hi' ? 'कोहरा' : 'Foggy';
                            else if (weatherCode >= 51 && weatherCode <= 67) desc = this.lang === 'hi' ? 'हल्की बारिश' : 'Drizzle/Rain';
                            else if (weatherCode >= 71 && weatherCode <= 86) desc = this.lang === 'hi' ? 'बर्फबारी' : 'Snowfall';
                            else if (weatherCode >= 95) desc = this.lang === 'hi' ? 'गरज के साथ बौछारें' : 'Thunderstorm';

                            // suggestions
                            let sug = '';
                            if (cur.temperature_2m > 30) {
                                sug = this.lang === 'hi' ? 'उच्च तापमान: फसल जल स्तर बनाए रखें और शाम को सिंचाई करें।' : 'High temp: Ensure crop hydration. Schedule watering for early morning/evening.';
                            } else if (cur.relative_humidity_2m > 80) {
                                sug = this.lang === 'hi' ? 'उच्च आर्द्रता: कीटों और कवक हमलों के लिए निगरानी रखें।' : 'High humidity: Monitor fields closely for fungal outbreaks or pest cycles.';
                            } else {
                                sug = this.lang === 'hi' ? 'मौसम फसल विकास के लिए अनुकूल है। सतत पद्धतियां अपनाएं।' : 'Conditions are optimal for crop growth. Follow sustainable practices.';
                            }

                            const weatherObj = {
                                location: this.lang === 'hi' ? 'आपका स्थान' : 'Your Field Coordinates',
                                description: desc,
                                temp: Math.round(cur.temperature_2m),
                                humidity: cur.relative_humidity_2m,
                                rainProb: cur.precipitation_probability || 0,
                                suggestion: sug
                            };

                            this.appendWeatherCard(weatherObj, null);
                        })
                        .catch(err => {
                            console.error(err);
                            this.appendWeatherCard(null, this.lang === 'hi' ? 'मौसम एपीआई त्रुटि।' : 'Failed to query Weather Service API.');
                        });
                },
                (error) => {
                    console.error(error);
                    this.appendWeatherCard(null, this.lang === 'hi' ? 'स्थान एक्सेस ब्लॉक किया गया।' : 'Geolocation access denied by user.');
                }
            );
        },

        appendWeatherCard(weatherData, errorText) {
            if (errorText) {
                this.messages.push({
                    sender: 'bot',
                    text: `⚠️ **Weather Retrieval Failed:** ${errorText}`,
                    time: this.getFormattedTime()
                });
            } else {
                this.messages.push({
                    sender: 'bot',
                    text: this.lang === 'hi' ? 'यहाँ आपका लाइव कृषि मौसम विश्लेषण है:' : 'Here is your current agricultural weather brief:',
                    time: this.getFormattedTime(),
                    isWeather: true,
                    weatherData: weatherData
                });
            }
            this.saveHistory();
            this.$nextTick(() => this.scrollToBottom());
        },

        renderMarkdown(text) {
            if (!text) return '';
            let html = text;
            
            // Format Bold (**text**)
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // Format bullet lists (- text)
            html = html.replace(/^\s*-\s+(.*?)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*?<\/li>)/gs, '<ul>$1</ul>');
            // Cleanup double nested <ul>s
            html = html.replace(/<\/ul>\s*<ul>/g, '');

            // Format numbered lists (1. text)
            html = html.replace(/^\s*\d+\.\s+(.*?)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*?<\/li>)/gs, '<ol>$1</ol>');
            html = html.replace(/<\/ol>\s*<ol>/g, '');

            // Convert double newlines to paragraphs
            html = html.replace(/\n\n/g, '<p></p>');
            // Convert single newlines to br
            html = html.replace(/\n/g, '<br>');

            return html;
        },

        saveHistory() {
            const userId = "{{ auth()->id() }}";
            const historyToSave = this.messages.filter(msg => !msg.loading);
            localStorage.setItem('chatbot-history-' + userId, JSON.stringify(historyToSave));
        },

        downloadPdf() {
            if (this.messages.length === 0) {
                alert(this.lang === 'hi' ? 'डाउनलोड करने के लिए कोई इतिहास नहीं है।' : 'No chat history to download.');
                return;
            }

            // Load jsPDF from CDN dynamically
            const pdfScriptUrl = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            
            if (window.jspdf) {
                this.generateAndSavePdf();
            } else {
                const script = document.createElement('script');
                script.src = pdfScriptUrl;
                script.onload = () => this.generateAndSavePdf();
                document.head.appendChild(script);
            }
        },

        generateAndSavePdf() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Header Section
            doc.setFillColor(22, 163, 74);
            doc.rect(0, 0, 210, 40, 'F');

            doc.setTextColor(255, 255, 255);
            doc.setFontSize(22);
            doc.setFont('Helvetica', 'bold');
            doc.text('CropsCycle AI Assistant', 20, 25);
            
            doc.setFontSize(10);
            doc.setFont('Helvetica', 'normal');
            doc.text(`Generated: ${new Date().toLocaleString()}`, 140, 28);

            // Conversations
            doc.setTextColor(15, 23, 42);
            doc.setFontSize(12);
            doc.setFont('Helvetica', 'bold');
            doc.text('Agricultural Conversation Briefing', 20, 55);

            let y = 65;
            doc.setFont('Helvetica', 'normal');
            doc.setFontSize(10);

            this.messages.forEach((msg) => {
                if (msg.loading) return;

                const senderLabel = msg.sender === 'user' ? 'Farmer:' : 'AI Assistant:';
                
                // Set text color according to sender
                if (msg.sender === 'user') {
                    doc.setTextColor(37, 99, 235);
                } else {
                    doc.setTextColor(22, 163, 74);
                }
                
                doc.setFont('Helvetica', 'bold');
                doc.text(senderLabel, 20, y);
                
                doc.setTextColor(15, 23, 42);
                doc.setFont('Helvetica', 'normal');

                // Split long lines
                const textLines = doc.splitTextToSize(msg.text, 170);
                doc.text(textLines, 45, y);
                
                y += (textLines.length * 5) + 6;

                // Page break if needed
                if (y > 275) {
                    doc.addPage();
                    y = 25;
                }
            });

            // Save PDF
            doc.save(`cropscycle-chat-report-${new Date().toISOString().slice(0,10)}.pdf`);
        }
    };
}
</script>
