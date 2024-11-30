@props(['businesses', 'selected', 'route'])

<div class="relative">
    <select onchange="window.location.href=this.value" class="block appearance-none bg-white border border-gray-300 rounded-md py-2 pl-3 pr-10 text-base focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        @foreach($businesses as $business)
            <option value="{{ route($route, ['businessId' => $business->id]) }}"
                    {{ $business->id == $selected->id ? 'selected' : '' }}>
                {{ $business->name }}
            </option>
        @endforeach
    </select>
</div>