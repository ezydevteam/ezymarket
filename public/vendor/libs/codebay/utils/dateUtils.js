(function() {
    'use strict';

    function dateUtils(dateString, context = 'conversation') {
        const date = new Date(dateString);
        const now = new Date();
        
        const isToday = (d) => {
            const today = new Date();
            return d.getDate() === today.getDate() &&
                   d.getMonth() === today.getMonth() &&
                   d.getFullYear() === today.getFullYear();
        };
        
        const isYesterday = (d) => {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            return d.getDate() === yesterday.getDate() &&
                   d.getMonth() === yesterday.getMonth() &&
                   d.getFullYear() === yesterday.getFullYear();
        };
        
        const getRelativeTime = (diffSeconds, prefix = '', full = false, defaultText = false) => {
			const absDiff = Math.abs(diffSeconds);

			const format = (value, short, fullSingular, fullPlural) => {
				if (full) {
					return `${value} ${value === 1 ? fullSingular : fullPlural} ago`;
				}
				return `${prefix} ${value}${short} ago`;
			};

			if (absDiff < 60) return `${defaultText ? 'Few seconds ago' : 'Just now'}`;

			if (absDiff < 3600) {
				const minutes = Math.floor(absDiff / 60);
				return format(minutes, 'm', 'minute', 'minutes');
			}

			if (absDiff < 86400) {
				const hours = Math.floor(absDiff / 3600);
				return format(hours, 'h', 'hour', 'hours');
			}

			if (absDiff < 604800) {
				const days = Math.floor(absDiff / 86400);
				return format(days, 'd', 'day', 'days');
			}

			if (absDiff < 2592000) {
				const weeks = Math.floor(absDiff / 604800);
				return format(weeks, 'w', 'week', 'weeks');
			}

			if (absDiff < 31536000) {
				const months = Math.floor(absDiff / 2592000);
				return format(months, 'mo', 'month', 'months');
			}

			if (absDiff < 315360000) {
				const years = Math.floor(absDiff / 31536000);
				return format(years, 'y', 'year', 'years');
			}

			return null;
		};
        
        const formatTime = (d) => {
            return d.toLocaleTimeString([], { 
                hour: 'numeric', 
                minute: '2-digit', 
                hour12: true 
            });
        };
        
        const formatFullDate = (d) => {
            return d.toLocaleDateString([], { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
        };
        
        const diffSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);
        switch (context) {
            case 'conversation':
                if (isToday(date)) {
                    return `Today ${formatTime(date)}`;
                } else if (isYesterday(date)) {
                    return `Yesterday ${formatTime(date)}`;
                } else {
                    return `${formatFullDate(date)} ${formatTime(date)}`;
                }
                
            case 'time-only':
                return formatTime(date);
                
            case 'chat-header':
                return getRelativeTime(diffSeconds, 'Active', false, true);
                
            case 'date-header':
                if (isToday(date)) {
                    return 'Today';
                } else if (isYesterday(date)) {
                    return 'Yesterday';
                } else {
                    const weekAgo = new Date();
                    weekAgo.setDate(now.getDate() - 7);
                    
                    if (date > weekAgo) {
                        return date.toLocaleDateString([], { 
                            weekday: 'long',
                            month: 'short', 
                            day: 'numeric'
                        });
                    } else {
                        return date.toLocaleDateString([], { 
                            weekday: 'long',
                            month: 'long', 
                            day: 'numeric', 
                            year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined 
                        });
                    }
                }
                
            case 'short-date':
                if (isToday(date)) {
                    return formatTime(date);
                } else if (isYesterday(date)) {
                    return 'Yesterday';
                } else if (date.getFullYear() === now.getFullYear()) {
                    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
                } else {
                    return date.toLocaleDateString([], { month: 'short', day: 'numeric', year: '2-digit' });
                }
                
            case 'full-date':
                return date.toLocaleDateString([], { 
                    weekday: 'short',
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                
            case 'relative':
                if (diffSeconds < 86400) {
                    return getRelativeTime(diffSeconds);
                } else if (isYesterday(date)) {
                    return 'Yesterday';
                } else if (diffSeconds < 604800) {
                    return date.toLocaleDateString([], { weekday: 'long' });
                } else {
                    return formatFullDate(date);
                }
                
            case 'short-relative':
                return getRelativeTime(diffSeconds);
                
            default:
                if (isToday(date)) {
                    return formatTime(date);
                } else if (isYesterday(date)) {
                    return `Yesterday ${formatTime(date)}`;
                } else {
                    return `${formatFullDate(date)} ${formatTime(date)}`;
                }
        }
    }
    
    window.dateUtils = dateUtils;
    
})();