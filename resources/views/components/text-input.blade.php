@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#CFAF53] focus:ring-[#CFAF53]/40 rounded-md shadow-sm']) }}>
