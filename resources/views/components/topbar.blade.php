@php
    $user = auth()->user();
    $recentNotifications = $recentNotifications ?? collect();
    $localeOptions = [
        'en' => ['label' => 'English',     'flag' => '🇬🇧'],
        'hi' => ['label' => 'हिंदी',       'flag' => '🇮🇳'],
        'pa' => ['label' => 'ਪੰਜਾਬੀ',      'flag' => '🇮🇳'],
        'gu' => ['label' => 'ગુજરાતી',     'flag' => '🇮🇳'],
        'fr' => ['label' => 'Français',    'flag' => '🇫🇷'],
    ];
@endphp

<header class="app-topbar">
    <div class="topbar-left">
        <button class="topbar-menu-btn d-lg-none" type="button" onclick="openSidebar()" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar-title d-none d-md-block">
            <nav aria-label="breadcrumb" class="topbar-breadcrumb">
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    <span>Smart Agriculture Platform</span>
                    <i class="bi bi-dot"></i>
                    <span>@yield('title', 'Dashboard')</span>
                @endif
            </nav>
        </div>
    </div>

    <div class="topbar-center">
        <form class="topbar-search" role="search" onsubmit="return false;" style="position: relative;" autocomplete="off">
            <i class="bi bi-search topbar-search-icon"></i>
            <input type="text" class="topbar-search-input" placeholder="{{ __('Search anything...') }}" id="globalSearch">
            <button type="button" class="voice-search-btn" id="voiceSearchBtn" title="Voice search">
                <i class="bi bi-mic"></i>
            </button>
            <span class="topbar-search-shortcut">/</span>
            <div id="searchSuggestions" class="search-suggestions-dropdown" style="display: none;"></div>
        </form>
    </div>

    <div class="topbar-actions">
        <div class="ui-dropdown">
            <button class="topbar-icon-btn" type="button" data-dropdown-trigger="languageMenu" aria-haspopup="true" aria-expanded="false" title="Language switcher">
                <i class="bi bi-translate"></i>
            </button>
            <div class="ui-dropdown-menu locale-dropdown" id="languageMenu">
                <div class="dropdown-menu-head">
                    <strong>Language</strong>
                    <span>Switch interface locale</span>
                </div>
                @foreach($localeOptions as $code => $locale)
                    <form action="{{ route('settings.locale.switch') }}" method="POST">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $code }}">
                        <button type="submit" class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}">
                            <span class="dropdown-flag">{{ $locale['flag'] }}</span>
                            <span>{{ $locale['label'] }}</span>
                            @if(app()->getLocale() === $code)
                                <i class="bi bi-check2 ms-auto"></i>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <div class="ui-dropdown">
            <button class="topbar-icon-btn" type="button" data-dropdown-trigger="notificationMenu" aria-haspopup="true" aria-expanded="false" title="Notifications" id="notifBell">
                <i class="bi bi-bell-fill"></i>
                <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
            </button>

            <div class="ui-dropdown-menu notif-dropdown" id="notificationMenu">
                <div class="notif-header">
                    <div>
                        <strong>Notifications</strong>
                        <span>Recent platform activity</span>
                    </div>
                    <form id="markAllForm" action="{{ route('notifications.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="notif-mark-all">Mark all read</button>
                    </form>
                </div>
                <div class="notif-scroll" id="notifList">
                    @forelse($recentNotifications as $notification)
                        @php
                            $data = $notification->data;
                            $type = strtolower(($data['title'] ?? '') . ' ' . ($data['message'] ?? ''));
                            $icon = $data['icon'] ?? (str_contains($type, 'dataset') ? 'database-fill' : (str_contains($type, 'analysis') ? 'flower1' : 'exclamation-triangle-fill'));
                        @endphp
                        <div class="notif-item {{ $notification->read_at ? '' : 'unread' }}">
                            <div class="notif-item-icon"><i class="bi bi-{{ $icon }}"></i></div>
                            <div class="notif-item-copy">
                                <div class="notif-item-title-row">
                                    <strong>{{ $data['title'] ?? 'Notification' }}</strong>
                                    @if(!$notification->read_at)<span class="notif-item-dot"></span>@endif
                                </div>
                                <p>{{ $data['message'] ?? 'A new system event has been logged.' }}</p>
                                <div class="notif-item-meta">
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="notif-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>No new notifications</p>
                        </div>
                    @endforelse
                </div>
                <div class="notif-footer">
                    <a href="{{ route('notifications.index') }}" class="btn-outline btn-sm w-100 justify-content-center">View all</a>
                </div>
            </div>
        </div>

        <div class="ui-dropdown">
            <button class="topbar-avatar-btn" type="button" data-dropdown-trigger="profileMenu" aria-haspopup="true" aria-expanded="false">
                <img src="{{ $user->avatar_url }}" alt="Profile" class="topbar-avatar">
                <span class="topbar-user-copy d-none d-sm-grid">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ ucfirst($user->role) }}</small>
                </span>
                <i class="bi bi-chevron-down ms-1"></i>
            </button>

            <div class="ui-dropdown-menu profile-dropdown" id="profileMenu">
                <div class="profile-card-head">
                    <img src="{{ $user->avatar_url }}" alt="Avatar" class="profile-card-avatar">
                    <div class="profile-card-copy">
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->email }}</span>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('settings.profile') }}"><i class="bi bi-person"></i><span>Profile</span></a>
                <a class="dropdown-item" href="{{ route('settings.security') }}"><i class="bi bi-shield-lock"></i><span>Security</span></a>
                <form id="themeToggleForm" action="{{ route('settings.theme.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item w-100">
                        @if(auth()->user()->theme === 'dark')
                            <i class="bi bi-sun"></i><span>Light Mode</span>
                        @else
                            <i class="bi bi-moon"></i><span>Dark Mode</span>
                        @endif
                    </button>
                </form>
                <div class="dropdown-divider"></div>
                <form id="logoutForm" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger w-100"><i class="bi bi-box-arrow-right"></i><span>Logout</span></button>
                </form>
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('globalSearch');
    const suggestionsDropdown = document.getElementById('searchSuggestions');
    const notifBadge = document.getElementById('notifBadge');
    const sidebarDot = document.getElementById('sidebarUnreadDot');
    const notifList = document.getElementById('notifList');
    const markAllForm = document.getElementById('markAllForm');
    
    // Seen notification IDs Set
    const seenNotificationIds = new Set();
    let isInitialLoad = true;

    // CSRF Token helper
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // ────────────────────────────────────────────────────────
    // Global Search Autocomplete Suggestions
    // ────────────────────────────────────────────────────────
    let debounceSearchTimeout;
    let suggestionsList = [];
    let focusedSuggestionIndex = -1;

    // Load recent searches from localStorage
    function getRecentSearches() {
        try {
            return JSON.parse(localStorage.getItem('recent_searches')) || [];
        } catch (e) {
            return [];
        }
    }

    // Save search item to recent searches
    function saveRecentSearch(item) {
        let recent = getRecentSearches();
        // Remove existing item with same url to prevent duplicate
        recent = recent.filter(r => r.url !== item.url);
        // Prepend new item
        recent.unshift(item);
        // Limit to 5
        if (recent.length > 5) {
            recent = recent.slice(0, 5);
        }
        localStorage.setItem('recent_searches', JSON.stringify(recent));
    }

    // Delete single recent search item
    function deleteRecentSearch(index) {
        let recent = getRecentSearches();
        recent.splice(index, 1);
        localStorage.setItem('recent_searches', JSON.stringify(recent));
    }

    // Clear all recent searches
    function clearRecentSearches() {
        localStorage.removeItem('recent_searches');
    }

    // Escape HTML to prevent XSS
    function escapeHtml(str) {
        if (!str) return '';
        return str.toString()
                  .replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    // Highlight query matches
    function highlightText(text, query) {
        if (!text) return '';
        const escapedText = escapeHtml(text);
        if (!query) return escapedText;
        const escapedQuery = escapeHtml(query).replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return escapedText.replace(regex, '<mark class="search-highlight">$1</mark>');
    }

    function executeItemSelection(item) {
        // Save to recent searches
        saveRecentSearch({
            title: item.title,
            subtitle: item.subtitle,
            url: item.url,
            icon: item.icon,
            type: item.type
        });

        // Hide dropdown and clear search
        suggestionsDropdown.style.display = 'none';
        searchInput.value = '';

        // Execute action or navigate
        if (item.url === '#toggle-theme') {
            const form = document.getElementById('themeToggleForm');
            if (form) {
                form.submit();
            } else {
                console.error("Theme toggle form not found.");
            }
        } else if (item.url === '#logout') {
            const form = document.getElementById('logoutForm');
            if (form) {
                form.submit();
            } else {
                console.error("Logout form not found.");
            }
        } else {
            window.location.href = item.url;
        }
    }

    function renderSuggestions(items, query = '') {
        suggestionsList = items;
        focusedSuggestionIndex = -1;

        if (items.length === 0) {
            if (query) {
                suggestionsDropdown.innerHTML = `
                    <div class="search-suggestion-empty">
                        No suggestions found for "${escapeHtml(query)}"
                    </div>
                `;
                suggestionsDropdown.style.display = 'block';
            } else {
                suggestionsDropdown.style.display = 'none';
                suggestionsDropdown.innerHTML = '';
            }
            return;
        }

        suggestionsDropdown.innerHTML = '';
        suggestionsDropdown.style.display = 'block';

        // If rendering recent searches (empty query), prepend header
        if (!query) {
            const header = document.createElement('div');
            header.className = 'search-recent-header';
            header.innerHTML = `
                <span>Recent Searches</span>
                <button type="button" class="search-recent-clear-btn" id="clearRecentSearchesBtn">Clear All</button>
            `;
            suggestionsDropdown.appendChild(header);

            // Clear all event listener
            header.querySelector('#clearRecentSearchesBtn').addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                clearRecentSearches();
                renderSuggestions([], '');
            });
        }

        items.forEach((item, index) => {
            const container = document.createElement('div');
            container.className = 'search-suggestion-item';
            container.setAttribute('data-index', index);
            
            const displayTitle = highlightText(item.title, query);
            const displaySubtitle = highlightText(item.subtitle, query);

            container.innerHTML = `
                <i class="bi bi-${item.icon || 'arrow-right-short'}"></i>
                <div class="search-suggestion-info" style="flex: 1; min-width: 0;">
                    <span class="search-suggestion-title">${displayTitle}</span>
                    <span class="search-suggestion-subtitle">${displaySubtitle}</span>
                </div>
                ${!query ? `
                    <button type="button" class="recent-delete-btn" title="Remove" data-recent-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                ` : ''}
            `;

            // Handle click
            container.addEventListener('click', function (e) {
                if (e.target.closest('.recent-delete-btn')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const rIndex = parseInt(e.target.closest('.recent-delete-btn').getAttribute('data-recent-index'));
                    deleteRecentSearch(rIndex);
                    const updatedRecent = getRecentSearches();
                    renderSuggestions(updatedRecent, '');
                    return;
                }

                executeItemSelection(item);
            });

            suggestionsDropdown.appendChild(container);
        });
    }

    if (searchInput && suggestionsDropdown) {
        // Show recent searches when focused and empty
        searchInput.addEventListener('focus', function () {
            const query = searchInput.value.trim();
            if (!query) {
                const recent = getRecentSearches();
                renderSuggestions(recent, '');
            }
        });

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceSearchTimeout);
            const query = searchInput.value.trim();

            if (query.length < 2) {
                if (query.length === 0) {
                    const recent = getRecentSearches();
                    renderSuggestions(recent, '');
                } else {
                    suggestionsDropdown.style.display = 'none';
                    suggestionsDropdown.innerHTML = '';
                }
                return;
            }

            debounceSearchTimeout = setTimeout(() => {
                suggestionsDropdown.style.display = 'block';
                suggestionsDropdown.innerHTML = `
                    <div class="search-suggestion-loading">
                        <i class="bi bi-arrow-repeat spin"></i> Searching...
                    </div>
                `;

                fetch(`/search/suggestions?q=${encodeURIComponent(query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    renderSuggestions(data.suggestions || [], query);
                })
                .catch(err => {
                    console.error("Autocomplete search error:", err);
                    suggestionsDropdown.style.display = 'none';
                });
            }, 300);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !suggestionsDropdown.contains(e.target)) {
                suggestionsDropdown.style.display = 'none';
            }
        });

        // Keyboard shortcuts: / and Ctrl+K / Cmd+K
        document.addEventListener('keydown', function (e) {
            // Forward slash / shortcut (only if not already focused in an input)
            if (e.key === '/' && document.activeElement !== searchInput && 
                !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                searchInput.focus();
                const recent = getRecentSearches();
                renderSuggestions(recent, '');
            }
            
            // Ctrl+K / Cmd+K shortcut
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                searchInput.focus();
                const recent = getRecentSearches();
                renderSuggestions(recent, '');
            }
        });

        // Keyboard navigation and actions
        searchInput.addEventListener('keydown', function (e) {
            const items = suggestionsDropdown.querySelectorAll('.search-suggestion-item');
            if (suggestionsDropdown.style.display === 'none' || items.length === 0) {
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                focusedSuggestionIndex = (focusedSuggestionIndex + 1) % items.length;
                updateFocus(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                focusedSuggestionIndex = (focusedSuggestionIndex - 1 + items.length) % items.length;
                updateFocus(items);
            } else if (e.key === 'Enter') {
                if (focusedSuggestionIndex >= 0 && focusedSuggestionIndex < suggestionsList.length) {
                    e.preventDefault();
                    executeItemSelection(suggestionsList[focusedSuggestionIndex]);
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                suggestionsDropdown.style.display = 'none';
                searchInput.blur();
            }
        });

        function updateFocus(domItems) {
            domItems.forEach((el, index) => {
                if (index === focusedSuggestionIndex) {
                    el.classList.add('focused');
                    el.scrollIntoView({ block: 'nearest' });
                } else {
                    el.classList.remove('focused');
                }
            });
        }
    }

    // Voice Search Feature using Web Speech API
    const voiceSearchBtn = document.getElementById('voiceSearchBtn');
    let recognition;

    if (voiceSearchBtn && searchInput) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            voiceSearchBtn.style.display = 'none';
        } else {
            recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';

            recognition.onstart = function () {
                voiceSearchBtn.classList.add('listening');
                searchInput.placeholder = 'Listening...';
            };

            recognition.onerror = function (e) {
                console.error("Speech recognition error:", e.error);
                let errMsg = 'Speech recognition failed.';
                if (e.error === 'no-speech') {
                    errMsg = 'No speech was detected. Try again.';
                } else if (e.error === 'not-allowed') {
                    errMsg = 'Microphone permission is blocked.';
                }
                showToast('Voice Search Error', errMsg, 'error');
                stopVoiceSearch();
            };

            recognition.onend = function () {
                stopVoiceSearch();
            };

            recognition.onresult = function (event) {
                const transcript = event.results[0][0].transcript;
                searchInput.value = transcript;
                // Dispatch input event to trigger autocomplete
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            };

            voiceSearchBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                if (voiceSearchBtn.classList.contains('listening')) {
                    recognition.stop();
                } else {
                    try {
                        recognition.start();
                    } catch (err) {
                        console.error("Failed to start speech recognition:", err);
                    }
                }
            });
        }
    }

    function stopVoiceSearch() {
        if (voiceSearchBtn) {
            voiceSearchBtn.classList.remove('listening');
        }
        if (searchInput) {
            searchInput.placeholder = 'Search anything...';
        }
    }

    // ────────────────────────────────────────────────────────
    // Real-Time Notification System (Poll & AJAX actions)
    // ────────────────────────────────────────────────────────
    function pollNotifications() {
        fetch('{{ route('notifications.poll') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            // Update unread count
            const count = data.count;
            if (count > 0) {
                notifBadge.textContent = count > 9 ? '9+' : count;
                notifBadge.style.display = 'flex';
                if (sidebarDot) sidebarDot.style.display = 'inline-flex';
            } else {
                notifBadge.style.display = 'none';
                if (sidebarDot) sidebarDot.style.display = 'none';
            }

            // Render notification items
            if (!notifList) return;

            if (!data.notifications || data.notifications.length === 0) {
                notifList.innerHTML = `
                    <div class="notif-empty">
                        <i class="bi bi-bell-slash"></i>
                        <p>No new notifications</p>
                    </div>
                `;
                isInitialLoad = false;
                return;
            }

            // Render notifications list HTML
            let html = '';
            data.notifications.forEach(notif => {
                // Instantly notify about new notifications (if it's a new ID and unread)
                if (!seenNotificationIds.has(notif.id)) {
                    seenNotificationIds.add(notif.id);
                    
                    if (!isInitialLoad && !notif.read_at) {
                        // Display stackable toast alert
                        showToast(notif.title, notif.message, notif.color || 'info', notif.url);
                        
                        // Play a clean soft notification tone
                        try {
                            const sound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-600.wav');
                            sound.volume = 0.4;
                            sound.play();
                        } catch (e) {
                            console.log("Audio play blocked by browser policy.");
                        }
                    }
                }

                // Determine icon & color class
                const isUnread = !notif.read_at;
                
                html += `
                    <div class="notif-item ${isUnread ? 'unread' : ''}" data-id="${notif.id}" style="display: flex; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--border-light); transition: background 200ms;">
                        <div class="notif-item-icon" style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: rgba(22, 163, 74, 0.1); color: var(--brand-green);">
                            <i class="bi bi-${notif.icon || 'bell'}"></i>
                        </div>
                        <div class="notif-item-copy" style="flex: 1; min-width: 0;">
                            <div class="notif-item-title-row" style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                <strong style="font-size: 0.85rem; color: var(--text-primary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">${notif.title}</strong>
                                ${isUnread ? '<span class="notif-item-dot" style="width: 7px; height: 7px; border-radius: 50%; background: var(--brand-green); flex-shrink: 0;"></span>' : ''}
                            </div>
                            <p style="font-size: 0.78rem; color: var(--text-secondary); margin: 2px 0; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${notif.message}</p>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px;">
                                <span class="notif-item-meta" style="font-size: 0.7rem; color: var(--text-muted);">${notif.created_at}</span>
                                ${notif.url && notif.url !== '#' ? `<a href="${notif.url}" style="font-size: 0.72rem; font-weight: 600; color: var(--brand-green); text-decoration: none;">View details</a>` : ''}
                            </div>
                        </div>
                        <div class="notif-item-actions" style="display: flex; flex-direction: column; gap: 4px; justify-content: center; align-items: center;">
                            ${isUnread ? `
                                <button class="btn-notif-action mark-read-btn" data-id="${notif.id}" title="Mark as read" style="background: transparent; border: none; padding: 4px; cursor: pointer; color: var(--brand-green); border-radius: 4px; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-check-lg" style="font-size: 1rem;"></i>
                                </button>
                            ` : ''}
                            <button class="btn-notif-action delete-btn" data-id="${notif.id}" title="Delete notification" style="background: transparent; border: none; padding: 4px; cursor: pointer; color: var(--text-muted); border-radius: 4px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            notifList.innerHTML = html;
            isInitialLoad = false;
            
            // Attach individual action event listeners
            attachNotificationActionListeners();
        })
        .catch(err => {
            console.error("Error polling notifications:", err);
        });
    }

    function attachNotificationActionListeners() {
        // Mark individual notification as read
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const id = this.getAttribute('data-id');
                
                fetch(`/notifications/${id}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        pollNotifications();
                    }
                })
                .catch(err => console.error("Error marking read:", err));
            });
        });

        // Delete individual notification
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const id = this.getAttribute('data-id');
                
                fetch(`/notifications/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        pollNotifications();
                    }
                })
                .catch(err => console.error("Error deleting notification:", err));
            });
        });
    }

    // Mark all read AJAX submission
    if (markAllForm) {
        markAllForm.addEventListener('submit', function (e) {
            e.preventDefault();
            
            fetch('{{ route('notifications.mark-all-read') }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', 'All notifications marked as read.', 'success');
                    pollNotifications();
                }
            })
            .catch(err => console.error("Error marking all read:", err));
        });
    }

    // Initial Poll
    pollNotifications();

    // Poll every 5 seconds for responsive real-time notifications
    window.setInterval(pollNotifications, 5000);
});
</script>
@endpush
