# Standalone Premium Global Search Component (React)

This is a premium, reusable React-based smart global search component built with React Router, Lucide Icons, and Vanilla CSS. It matches the look and feel of the Agriculture Crop Cycle Analysis platform (*CropsCycle*).

## Key Features

1. **Unified Global Autocomplete:** Matches navigation routing views, database records (crop cycles), and custom display actions.
2. **Keyboard Navigation Support:**
   - Focus search bar instantly using `/` or `Ctrl+K` / `Cmd+K`.
   - Move selection highlighting up and down using the `ArrowUp` and `ArrowDown` keys.
   - Close the suggestion menu with `Escape`.
   - Submit selections and navigate with `Enter`.
3. **Recent Search History (`localStorage`):**
   - Shows the last 5 successful search selections when the query is empty.
   - Features a "Clear All" history option and individual "Delete" buttons for specific search items.
4. **Live Query Highlighting:** Boldly highlights matched search segments while safely escaping HTML characters to prevent XSS vulnerability risks.
5. **Voice Recognition (Web Speech API):**
   - Enables hands-free searching with speech-to-text.
   - Triggers clean microphone pulsing animations and automatic input field insertion.
6. **Harmonious Theme Support:** Styled with responsive light and dark themes using custom CSS variables and glassmorphism styling.

## Directory Structure

```
react-global-search/
├── index.html
├── package.json
├── vite.config.js
├── README.md
└── src/
    ├── main.jsx
    ├── App.jsx
    ├── index.css
    └── components/
        └── GlobalSearch.jsx
```

## Getting Started

### Prerequisites
Make sure you have Node.js (version 18 or above) installed on your system.

### 1. Install Dependencies
Navigate to the directory and run the install command:
```bash
cd react-global-search
npm install
```

### 2. Launch Dev Server
Start the local development server:
```bash
npm run dev
```
The server will start at `http://localhost:3000` and automatically open your default browser.
