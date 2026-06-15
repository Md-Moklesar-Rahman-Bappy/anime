@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:focus:border-purple-500 dark:focus:ring-purple-500']) }}>
