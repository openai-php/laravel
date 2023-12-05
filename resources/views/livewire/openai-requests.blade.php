<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
            name="{{ $this->label }}"
            title="Time: {{ number_format($time) }}ms; Run at: {{ $runAt }};"
            details="past {{ $this->periodForHumans() }}"
    >
        <x-slot:icon>
            <svg fill="#000000" width="800px" height="800px" viewBox="0 0 24 24" role="img"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z"/>
            </svg>
        </x-slot:icon>
        <x-slot:actions>
            @if(!$this->type)
                <x-pulse::select
                        wire:model.live="openaiRequests"
                        label="By"
                        :options="[
                        'user' => 'Users',
                        'endpoint' => 'API endpoint',
                    ]"
                        class="flex-1"
                        @change="loading = true"
                />
            @endif
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($requests->isEmpty())
            <x-pulse::no-results/>
        @else
            @if($aggregate === 'user')
                <div class="grid grid-cols-1 @lg:grid-cols-2 @3xl:grid-cols-3 @6xl:grid-cols-4 gap-2">
                    @foreach ($requests as $requestCount)
                        <x-pulse::user-card wire:key="{{ $requestCount->user->id.$this->period }}"
                                            :name="$requestCount->user->name" :extra="$requestCount->user->extra">
                            @if ($requestCount->user->avatar ?? false)
                                <x-slot:avatar>
                                    <img height="32" width="32" src="{{ $requestCount->user->avatar }}" loading="lazy"
                                         class="rounded-full">
                                </x-slot:avatar>
                            @endif

                            <x-slot:stats>
                                @php
                                    $sampleRate = $config['sample_rate'];
                                @endphp

                                @if ($sampleRate < 1)
                                    <span title="Sample rate: {{ $sampleRate }}, Raw value: {{ number_format($requestCount->count) }}">~{{ number_format($requestCount->count * (1 / $sampleRate)) }}</span>
                                @else
                                    {{ number_format($requestCount->count) }}
                                @endif
                            </x-slot:stats>
                        </x-pulse::user-card>
                    @endforeach
                </div>
            @else
                <x-pulse::table>
                    <colgroup>
                        <col width="0%"/>
                        <col width="100%"/>
                        <col width="0%"/>
                    </colgroup>
                    <x-pulse::thead>
                        <tr>
                            <x-pulse::th>Method</x-pulse::th>
                            <x-pulse::th>Uri</x-pulse::th>
                            <x-pulse::th class="text-right">Count</x-pulse::th>
                        </tr>
                    </x-pulse::thead>
                    <tbody>
                    @foreach ($requests->take(10) as $request)
                        <tr class="h-2 first:h-0"></tr>
                        <tr wire:key="{{ $request->method.$request->uri.$this->period }}">
                            <x-pulse::td>
                                <x-pulse::http-method-badge :method="$request->method"/>
                            </x-pulse::td>
                            <x-pulse::td class="overflow-hidden max-w-[1px]">
                                <code class="block text-xs text-gray-900 dark:text-gray-100 truncate"
                                      title="{{ $request->uri }}">
                                    /{{ $request->uri }}
                                </code>
                            </x-pulse::td>
                            <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                @if ($config['sample_rate'] < 1)
                                    <span title="Sample rate: {{ $config['sample_rate'] }}, Raw value: {{ number_format($request->count) }}">~{{ number_format($request->count * (1 / $config['sample_rate'])) }}</span>
                                @else
                                    {{ number_format($request->count) }}
                                @endif
                            </x-pulse::td>
                        </tr>
                    @endforeach
                    </tbody>
                </x-pulse::table>

                @if ($requests->count() > 10)
                    <div class="mt-2 text-xs text-gray-400 text-center">Limited to 10 entries</div>
                @endif
            @endif
        @endif
    </x-pulse::scroll>
</x-pulse::card>
