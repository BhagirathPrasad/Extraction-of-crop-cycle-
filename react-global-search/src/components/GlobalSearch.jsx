import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import * as Icons from 'lucide-react';

const SEARCHABLE_ITEMS = [
  { title: "Dashboard", subtitle: "Overview of crop cycle parameters, NDVI trends, and predictions", url: "/dashboard", icon: "LayoutGrid", type: "page" },
  { title: "Analytics & AI", subtitle: "Predict crop yield and analyze satellite intelligence data", url: "/analytics", icon: "TrendingUp", type: "page" },
  { title: "Datasets", subtitle: "Upload, view, and reprocess multi-temporal datasets", url: "/datasets", icon: "Database", type: "page" },
  { title: "Crop Cycle Analysis", subtitle: "Manage extracted crop cycle parameters and NDVI timelines", url: "/crop-cycles", icon: "RefreshCw", type: "page" },
  { title: "Search Cycles", subtitle: "Perform fast dynamic search and filters on crop cycles", url: "/search", icon: "Search", type: "page" },
  { title: "Reports", subtitle: "Export crop intelligence insights to PDF and Excel spreadsheets", url: "/reports", icon: "FileBarChart", type: "page" },
  { title: "Notifications", subtitle: "Recent activity, user login alerts, and system notifications", url: "/notifications", icon: "Bell", type: "page" },
  { title: "Settings", subtitle: "Configure your personal profile settings and password security", url: "/settings", icon: "Settings", type: "page" },
  { title: "Users", subtitle: "Manage user accounts, roles, and status configurations", url: "/users", icon: "Users", type: "page" },
  { title: "Activity Logs", subtitle: "View administrative and user audit logs", url: "/activity-logs", icon: "History", type: "page" },
  { title: "Toggle Theme (Light / Dark Mode)", subtitle: "Switch between light and dark display theme preferences", url: "#toggle-theme", icon: "Sun", type: "action" },
  { title: "Logout", subtitle: "Securely end your session and sign out", url: "#logout", icon: "LogOut", type: "action" },
  { title: "Wheat (GW-496)", subtitle: "Madhya Pradesh • Rabi 2025 • Clay Loam", url: "/crop-cycles/1", icon: "Sprout", type: "crop_cycle" },
  { title: "Rice (Basmati 370)", subtitle: "Punjab • Kharif 2025 • Alluvial", url: "/crop-cycles/2", icon: "Sprout", type: "crop_cycle" },
  { title: "Cotton (Bt Cotton)", subtitle: "Gujarat • Kharif 2025 • Black Soil", url: "/crop-cycles/3", icon: "Sprout", type: "crop_cycle" },
  { title: "Maize (HQPM-1)", subtitle: "Bihar • Kharif 2025 • Sandy Loam", url: "/crop-cycles/4", icon: "Sprout", type: "crop_cycle" },
  { title: "Sugarcane (Co 86032)", subtitle: "Maharashtra • Perennial 2025 • Clayey", url: "/crop-cycles/5", icon: "Sprout", type: "crop_cycle" },
  { title: "Soybean (JS 335)", subtitle: "Madhya Pradesh • Kharif 2025 • Black Cotton", url: "/crop-cycles/6", icon: "Sprout", type: "crop_cycle" }
];

export default function GlobalSearch({ theme, toggleTheme, triggerToast }) {
  const [query, setQuery] = useState('');
  const [isOpen, setIsOpen] = useState(false);
  const [suggestions, setSuggestions] = useState([]);
  const [focusedIndex, setFocusedIndex] = useState(-1);
  const [isListening, setIsListening] = useState(false);

  const navigate = useNavigate();
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);
  const recognitionRef = useRef(null);

  // Load recent searches on mount or update
  const getRecentSearches = () => {
    try {
      return JSON.parse(localStorage.getItem('recent_searches')) || [];
    } catch (e) {
      return [];
    }
  };

  // Setup Web Speech API for voice search
  useEffect(() => {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
      const rec = new SpeechRecognition();
      rec.continuous = false;
      rec.interimResults = false;
      rec.lang = 'en-US';

      rec.onstart = () => {
        setIsListening(true);
        triggerToast('info', 'Listening to voice search input...');
      };

      rec.onerror = (e) => {
        console.error("Speech recognition error:", e.error);
        let errMsg = 'Speech recognition failed.';
        if (e.error === 'no-speech') errMsg = 'No speech detected. Try again.';
        if (e.error === 'not-allowed') errMsg = 'Microphone permission blocked.';
        triggerToast('error', errMsg);
        setIsListening(false);
      };

      rec.onend = () => {
        setIsListening(false);
      };

      rec.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        setQuery(transcript);
        inputRef.current?.focus();
      };

      recognitionRef.current = rec;
    }
  }, [triggerToast]);

  // Bind shortcuts: '/' and 'Ctrl+K' / 'Cmd+K'
  useEffect(() => {
    const handleGlobalShortcuts = (e) => {
      // Forward Slash
      if (e.key === '/' && document.activeElement !== inputRef.current &&
          !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
        e.preventDefault();
        inputRef.current?.focus();
        setIsOpen(true);
      }

      // Ctrl+K / Cmd+K
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        inputRef.current?.focus();
        setIsOpen(true);
      }
    };

    window.addEventListener('keydown', handleGlobalShortcuts);
    return () => window.removeEventListener('keydown', handleGlobalShortcuts);
  }, []);

  // Filter logic on query change
  useEffect(() => {
    if (!query.trim()) {
      setSuggestions(getRecentSearches());
      return;
    }

    const filtered = SEARCHABLE_ITEMS.filter(item =>
      item.title.toLowerCase().includes(query.toLowerCase()) ||
      item.subtitle.toLowerCase().includes(query.toLowerCase())
    );
    setSuggestions(filtered);
    setFocusedIndex(-1);
  }, [query]);

  // Click outside to close dropdown listener
  useEffect(() => {
    const handleClickOutside = (e) => {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target) &&
          inputRef.current && !inputRef.current.contains(e.target)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Save selection to history
  const saveRecentSearch = (item) => {
    let recent = getRecentSearches();
    recent = recent.filter(r => r.url !== item.url);
    recent.unshift(item);
    if (recent.length > 5) recent = recent.slice(0, 5);
    localStorage.setItem('recent_searches', JSON.stringify(recent));
  };

  // Delete single history item
  const deleteRecentItem = (e, itemIndex) => {
    e.preventDefault();
    e.stopPropagation();
    let recent = getRecentSearches();
    recent.splice(itemIndex, 1);
    localStorage.setItem('recent_searches', JSON.stringify(recent));
    setSuggestions(recent);
    setFocusedIndex(-1);
  };

  // Clear all history
  const clearAllRecent = (e) => {
    e.preventDefault();
    e.stopPropagation();
    localStorage.removeItem('recent_searches');
    setSuggestions([]);
    setFocusedIndex(-1);
  };

  // Perform selected action or navigation
  const executeSelection = (item) => {
    saveRecentSearch({
      title: item.title,
      subtitle: item.subtitle,
      url: item.url,
      icon: item.icon,
      type: item.type
    });

    setIsOpen(false);
    setQuery('');

    if (item.url === '#toggle-theme') {
      toggleTheme();
      triggerToast('success', `Theme switched successfully!`);
    } else if (item.url === '#logout') {
      triggerToast('info', 'Logged out successfully! (Mock redirection)');
    } else {
      navigate(item.url);
      triggerToast('success', `Navigated to ${item.title}`);
    }
  };

  // Keyboard navigation handler
  const handleKeyDown = (e) => {
    if (!isOpen || suggestions.length === 0) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setFocusedIndex(prev => (prev + 1) % suggestions.length);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setFocusedIndex(prev => (prev - 1 + suggestions.length) % suggestions.length);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (focusedIndex >= 0 && focusedIndex < suggestions.length) {
        executeSelection(suggestions[focusedIndex]);
      }
    } else if (e.key === 'Escape') {
      e.preventDefault();
      setIsOpen(false);
      inputRef.current?.blur();
    }
  };

  // Highlight matches
  const renderHighlightedText = (text, match) => {
    if (!match) return <span>{text}</span>;
    const parts = text.split(new RegExp(`(${match.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')})`, 'gi'));
    return (
      <span>
        {parts.map((part, index) =>
          part.toLowerCase() === match.toLowerCase() ? (
            <mark key={index} className="search-highlight">{part}</mark>
          ) : (
            part
          )
        )}
      </span>
    );
  };

  // Voice recognition click handler
  const toggleVoiceListen = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!recognitionRef.current) {
      triggerToast('error', 'Voice search not supported in this browser.');
      return;
    }

    if (isListening) {
      recognitionRef.current.stop();
    } else {
      recognitionRef.current.start();
    }
  };

  return (
    <div className="search-container">
      <div className="search-form">
        <Icons.Search className="search-icon-left" size={18} />
        <input
          ref={inputRef}
          type="text"
          className="search-input"
          placeholder={isListening ? "Listening..." : "Search pages or crops... (e.g. wheat)"}
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          onFocus={() => {
            setIsOpen(true);
            if (!query.trim()) setSuggestions(getRecentSearches());
          }}
          onKeyDown={handleKeyDown}
        />
        
        {/* Voice Search Mic */}
        <button
          type="button"
          className={`voice-btn ${isListening ? 'listening' : ''}`}
          onClick={toggleVoiceListen}
          title="Voice Search"
        >
          {isListening ? <Icons.MicOff size={18} /> : <Icons.Mic size={18} />}
        </button>

        <span className="search-shortcut">/</span>
      </div>

      {/* Suggestion Dropdown */}
      {isOpen && (
        <div ref={dropdownRef} className="search-dropdown">
          {suggestions.length === 0 ? (
            query ? (
              <div className="search-empty">
                <Icons.SearchCode size={18} />
                <span>No results for "{query}"</span>
              </div>
            ) : null
          ) : (
            <>
              {/* Recent Search Header */}
              {!query.trim() && (
                <div className="search-recent-header">
                  <span>Recent Searches</span>
                  <button type="button" className="clear-history-btn" onClick={clearAllRecent}>
                    Clear All
                  </button>
                </div>
              )}

              {/* Items List */}
              {suggestions.map((item, index) => {
                // Get Icon component from name string
                const IconComp = Icons[item.icon] || Icons.Sprout;

                return (
                  <div
                    key={item.url + '-' + index}
                    className={`suggestion-item ${focusedIndex === index ? 'focused' : ''}`}
                    onClick={() => executeSelection(item)}
                    onMouseEnter={() => setFocusedIndex(index)}
                  >
                    <div className="suggestion-icon">
                      <IconComp size={16} />
                    </div>
                    <div className="suggestion-info">
                      <span className="suggestion-title">
                        {renderHighlightedText(item.title, query)}
                      </span>
                      <span className="suggestion-subtitle">
                        {renderHighlightedText(item.subtitle, query)}
                      </span>
                    </div>
                    
                    {/* Delete item from history (recent search view only) */}
                    {!query.trim() && (
                      <button
                        type="button"
                        className="delete-history-item-btn"
                        onClick={(e) => deleteRecentItem(e, index)}
                        title="Remove Search"
                      >
                        <Icons.X size={14} />
                      </button>
                    )}
                  </div>
                );
              })}
            </>
          )}
        </div>
      )}
    </div>
  );
}
