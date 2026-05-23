import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Link, useLocation } from 'react-router-dom';
import * as Icons from 'lucide-react';
import GlobalSearch from './components/GlobalSearch';

// Mock views showing page content
function MockPage({ title, icon, description }) {
  const IconComp = Icons[icon] || Icons.Sprout;
  return (
    <div className="page-view">
      <h2 className="page-view-title">
        <IconComp size={28} className="text-brand" />
        <span>{title}</span>
      </h2>
      <p className="page-view-desc">{description}</p>
    </div>
  );
}

function Home() {
  return (
    <div className="welcome-banner">
      <h1 className="welcome-title">Extraction of Crop Cycle Parameters</h1>
      <p className="welcome-text">
        Welcome to the crop cycle intelligence platform showroom. Press <kbd className="search-shortcut">/</kbd> or <kbd className="search-shortcut">Ctrl+K</kbd> to focus the global search bar, type keywords like <strong>Wheat</strong> or <strong>Dashboard</strong>, use your arrow keys to navigate, or click the mic button for voice commands.
      </p>
    </div>
  );
}

export default function App() {
  const [theme, setTheme] = useState('dark');
  const [toasts, setToasts] = useState([]);

  // Apply theme to document element
  useEffect(() => {
    document.documentElement.setAttribute('data-theme', theme);
  }, [theme]);

  const toggleTheme = () => {
    setTheme(prev => (prev === 'dark' ? 'light' : 'dark'));
  };

  const triggerToast = (type, message) => {
    const id = Date.now() + Math.random().toString(36).substr(2, 9);
    setToasts(prev => [...prev, { id, type, message }]);
    setTimeout(() => {
      setToasts(prev => prev.filter(t => t.id !== id));
    }, 4000);
  };

  return (
    <Router>
      <div className="app-container">
        {/* Navigation Topbar Header */}
        <header className="app-header">
          <Link to="/" className="brand">
            <div className="brand-icon">🌱</div>
            <span>CropsCycle</span>
          </Link>

          {/* Integration of Global Search */}
          <GlobalSearch
            theme={theme}
            toggleTheme={toggleTheme}
            triggerToast={triggerToast}
          />

          <button
            type="button"
            className="theme-toggle-header-btn"
            onClick={toggleTheme}
            title={`Switch to ${theme === 'dark' ? 'Light' : 'Dark'} Mode`}
          >
            {theme === 'dark' ? <Icons.Sun size={20} /> : <Icons.Moon size={20} />}
          </button>
        </header>

        {/* Showroom Content Area */}
        <main className="main-content">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/dashboard" element={<MockPage title="Dashboard" icon="LayoutGrid" description="NDVI cycle parameters overview, historical curves, and crop status charts." />} />
            <Route path="/analytics" element={<MockPage title="Analytics & AI" icon="TrendingUp" description="Machine learning crop yield predictions, NDVI anomaly triggers, and satellite indices." />} />
            <Route path="/datasets" element={<MockPage title="Datasets Hub" icon="Database" description="List of multi-temporal satellite imagery files, GeoTIFF coordinates, and process state queues." />} />
            <Route path="/crop-cycles" element={<MockPage title="Crop Cycle Analysis" icon="RefreshCw" description="List of cropped cycles, sowing metrics, peak greenness timing, and harvest window calculations." />} />
            <Route path="/search" element={<MockPage title="Search Cycles" icon="Search" description="Advanced filter tool for searching by crop type, regional boundary, soil properties, and harvest window." />} />
            <Route path="/reports" element={<MockPage title="Reports Center" icon="FileBarChart" description="Download yield estimate digests, historical trends, and cycle tables in PDF / XLS." />} />
            <Route path="/notifications" element={<MockPage title="Notifications Center" icon="Bell" description="Logs of login alerts, dataset ingestion successes, and platform telemetry warnings." />} />
            <Route path="/settings" element={<MockPage title="Account Settings" icon="Settings" description="Modify user settings, update passwords, toggle interface dark modes, and edit emails." />} />
            <Route path="/users" element={<MockPage title="User Accounts Directory" icon="Users" description="Administrative panel for reviewing user profile details, modifying roles, and suspending logins." />} />
            <Route path="/activity-logs" element={<MockPage title="Audit Activity Log" icon="History" description="Detailed timestamp history of administrative CRUD database operations and API calls." />} />
            
            {/* Mock Crop Cycle views */}
            <Route path="/crop-cycles/1" element={<MockPage title="Wheat (GW-496) Cycle Details" icon="Sprout" description="Madhya Pradesh region • Sown: 2025-11-10 • Harvested: 2026-03-25 • NDVI Peak: 0.82" />} />
            <Route path="/crop-cycles/2" element={<MockPage title="Rice (Basmati 370) Cycle Details" icon="Sprout" description="Punjab region • Sown: 2025-06-15 • Harvested: 2025-10-30 • NDVI Peak: 0.85" />} />
            <Route path="/crop-cycles/3" element={<MockPage title="Cotton (Bt Cotton) Cycle Details" icon="Sprout" description="Gujarat region • Sown: 2025-05-20 • Harvested: 2025-11-15 • NDVI Peak: 0.78" />} />
            <Route path="/crop-cycles/4" element={<MockPage title="Maize (HQPM-1) Cycle Details" icon="Sprout" description="Bihar region • Sown: 2025-06-05 • Harvested: 2025-09-25 • NDVI Peak: 0.79" />} />
            <Route path="/crop-cycles/5" element={<MockPage title="Sugarcane (Co 86032) Cycle Details" icon="Sprout" description="Maharashtra region • Sown: 2024-02-12 • Harvested: 2025-02-10 • NDVI Peak: 0.88" />} />
            <Route path="/crop-cycles/6" element={<MockPage title="Soybean (JS 335) Cycle Details" icon="Sprout" description="Madhya Pradesh region • Sown: 2025-06-20 • Harvested: 2025-10-05 • NDVI Peak: 0.81" />} />
          </Routes>
        </main>

        {/* Stackable Notification Toast Panel */}
        <div className="toast-overlay">
          {toasts.map((toast) => (
            <div key={toast.id} className={`toast-item ${toast.type}`}>
              {toast.type === 'error' && <Icons.AlertCircle size={16} />}
              {toast.type === 'info' && <Icons.Info size={16} />}
              {toast.type === 'success' && <Icons.CheckCircle size={16} />}
              <span>{toast.message}</span>
            </div>
          ))}
        </div>
      </div>
    </Router>
  );
}
