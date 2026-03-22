<form method="get" action="{{ route('program.search') }}" class="mb-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border-2 border-gray-200 dark:border-gray-700 focus-within:border-primary-500 dark:focus-within:border-primary-400 transition-all">
            <input
                type="text"
                name="title"
                placeholder="番組名で検索する"
                value="{{ request('title') }}"
                class="flex-1 px-4 py-3 text-base focus:outline-none bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 border-0"
            />
            <button
                type="submit"
                class="touch-target px-6 bg-primary-500 hover:bg-primary-600 text-white font-semibold transition flex items-center justify-center"
            >
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</form>
