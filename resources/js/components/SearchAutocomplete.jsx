import React, { useState, useEffect, useRef } from 'react';

export default function SearchAutocomplete({ route }) {
    const [query, setQuery] = useState('');
    const [suggestions, setSuggestions] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [showSuggestions, setShowSuggestions] = useState(false);
    const searchRef = useRef(null);

    useEffect(() => {
        const debounceTimer = setTimeout(() => {
            if (query.length >= 2) {
                fetchSuggestions(query);
            } else {
                setSuggestions([]);
                setShowSuggestions(false);
            }
        }, 300);

        return () => clearTimeout(debounceTimer);
    }, [query]);

    const fetchSuggestions = async (searchQuery) => {
        setIsLoading(true);
        try {
            const response = await fetch(`/api/programs/suggest?q=${encodeURIComponent(searchQuery)}`);
            const data = await response.json();
            setSuggestions(data.suggestions || []);
            setShowSuggestions(true);
        } catch (error) {
            console.error('Failed to fetch suggestions:', error);
            setSuggestions([]);
        } finally {
            setIsLoading(false);
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (query.trim()) {
            window.location.href = `${route}?title=${encodeURIComponent(query)}`;
        }
    };

    const selectSuggestion = (suggestion) => {
        setQuery(suggestion.title);
        setShowSuggestions(false);
        window.location.href = `${route}?title=${encodeURIComponent(suggestion.title)}`;
    };

    // 外側クリックで候補を閉じる
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (searchRef.current && !searchRef.current.contains(event.target)) {
                setShowSuggestions(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return (
        <div ref={searchRef} className="relative w-full max-w-2xl mx-auto">
            <form onSubmit={handleSubmit} className="relative">
                <div className="flex items-center bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border-2 border-transparent focus-within:border-primary-500 transition-all">
                    <input
                        type="text"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder="番組名を入力してください"
                        className="flex-1 px-4 py-3 md:px-6 md:py-4 text-base md:text-lg focus:outline-none bg-transparent text-gray-800 dark:text-white"
                        autoComplete="off"
                    />
                    {isLoading && (
                        <div className="px-3">
                            <i className="fas fa-spinner fa-spin text-gray-400"></i>
                        </div>
                    )}
                    <button
                        type="submit"
                        className="touch-target px-6 md:px-8 bg-primary-500 hover:bg-primary-600 text-white font-semibold transition"
                    >
                        <i className="fas fa-search"></i>
                        <span className="ml-2 hidden md:inline">検索</span>
                    </button>
                </div>
            </form>

            {/* オートコンプリート候補 */}
            {showSuggestions && suggestions.length > 0 && (
                <div className="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto">
                    {suggestions.map((suggestion, index) => (
                        <button
                            key={index}
                            onClick={() => selectSuggestion(suggestion)}
                            className="w-full text-left px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                        >
                            <div className="font-semibold text-gray-800 dark:text-white">
                                {suggestion.title}
                            </div>
                            {suggestion.cast && (
                                <div className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    <i className="fas fa-microphone mr-1"></i>
                                    {suggestion.cast}
                                </div>
                            )}
                            {suggestion.station_id && (
                                <div className="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    <i className="fas fa-broadcast-tower mr-1"></i>
                                    {suggestion.station_id}
                                </div>
                            )}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
