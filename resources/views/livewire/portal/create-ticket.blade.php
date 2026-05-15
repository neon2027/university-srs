<div class="max-w-3xl mx-auto">
    {{-- Progress bar --}}
    <div class="mb-8">
        <div class="mb-1.5 flex justify-between text-xs text-zinc-400">
            <span>Step {{ $step }} of 5</span>
            <span>{{ round(($step - 1) / 4 * 100) }}%</span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-zinc-700">
            <div class="h-1.5 rounded-full bg-[#0089CB] transition-all"
                 style="width: {{ round(($step - 1) / 4 * 100) }}%"></div>
        </div>
    </div>

    @if ($step === 1)
        <h2 class="mb-1 text-xl font-bold">Which office can help you?</h2>
        <p class="mb-6 text-sm text-zinc-400">Select the office that handles your request.</p>

        <div class="space-y-3">
            @foreach ($this->offices as $office)
                <button type="button" wire:key="office-{{ $office->id }}" wire:click="$set('officeId', {{ $office->id }})"
                        @class([
                            'w-full rounded-xl border px-5 py-4 text-left transition-colors',
                            'border-[#0089CB] bg-blue-900/20' => $officeId === $office->id,
                            'border-zinc-700/60 bg-zinc-800/50 hover:border-zinc-600' => $officeId !== $office->id,
                        ])>
                    <p class="font-semibold">{{ $office->name }}</p>
                    @if ($office->description)
                        <p class="mt-0.5 text-sm text-zinc-400">{{ $office->description }}</p>
                    @endif
                </button>
            @endforeach
        </div>
        @error('officeId') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror

        <div class="mt-8 flex justify-end">
            <button type="button" wire:click="nextStep"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#0089CB] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3]">
                <span>Next</span>
                <x-heroicon-o-arrow-right class="h-4 w-4" />
            </button>
        </div>
    @endif

    @if ($step === 2)
        <h2 class="mb-1 text-xl font-bold">What type of request?</h2>
        <p class="mb-6 text-sm text-zinc-400">Select a category.</p>

        <div class="space-y-3">
            @foreach ($this->categories as $category)
                <button type="button" wire:key="category-{{ $category->id }}" wire:click="$set('serviceCategoryId', {{ $category->id }})"
                        @class([
                            'w-full rounded-xl border px-5 py-4 text-left transition-colors',
                            'border-[#0089CB] bg-blue-900/20' => $serviceCategoryId === $category->id,
                            'border-zinc-700/60 bg-zinc-800/50 hover:border-zinc-600' => $serviceCategoryId !== $category->id,
                        ])>
                    <p class="font-semibold">{{ $category->name }}</p>
                    @if ($category->description)
                        <p class="mt-0.5 text-sm text-zinc-400">{{ $category->description }}</p>
                    @endif
                </button>
            @endforeach
        </div>
        @error('serviceCategoryId') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror

        <div class="mt-8 flex justify-between">
            <button type="button" wire:click="prevStep"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-semibold text-zinc-300 hover:border-zinc-500">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                <span>Back</span>
            </button>
            <button type="button" wire:click="nextStep"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#0089CB] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3]">
                <span>Next</span>
                <x-heroicon-o-arrow-right class="h-4 w-4" />
            </button>
        </div>
    @endif

    @if ($step === 3)
        <h2 class="mb-1 text-xl font-bold">Select a specific service</h2>
        <p class="mb-6 text-sm text-zinc-400">Choose the exact service you need.</p>

        <div class="space-y-3">
            @foreach ($this->serviceTypes as $type)
                <button type="button" wire:key="service-type-{{ $type->id }}" wire:click="$set('serviceTypeId', {{ $type->id }})"
                        @class([
                            'w-full rounded-xl border px-5 py-4 text-left transition-colors',
                            'border-[#0089CB] bg-blue-900/20' => $serviceTypeId === $type->id,
                            'border-zinc-700/60 bg-zinc-800/50 hover:border-zinc-600' => $serviceTypeId !== $type->id,
                        ])>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $type->name }}</p>
                            @if ($type->description)
                                <p class="mt-0.5 text-sm text-zinc-400">{{ $type->description }}</p>
                            @endif
                        </div>
                        @if ($type->sla_days)
                            <span class="shrink-0 rounded-full bg-zinc-700 px-2.5 py-0.5 text-xs text-zinc-300">
                                {{ $type->sla_days }}d SLA
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
        @error('serviceTypeId') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror

        <div class="mt-8 flex justify-between">
            <button type="button" wire:click="prevStep"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-semibold text-zinc-300 hover:border-zinc-500">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                <span>Back</span>
            </button>
            <button type="button" wire:click="nextStep"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#0089CB] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3]">
                <span>Next</span>
                <x-heroicon-o-arrow-right class="h-4 w-4" />
            </button>
        </div>
    @endif

    @if ($step === 4)
        <h2 class="mb-1 text-xl font-bold">Fill in the details</h2>
        <p class="mb-6 text-sm text-zinc-400">Answer the questions below to complete your request.</p>

        <div class="space-y-5">
            @foreach ($this->fields as $field)
                <div wire:key="field-{{ $field->id }}">
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">
                        {{ $field->label }}
                        @if ($field->is_required)
                            <span class="text-red-400">*</span>
                        @endif
                    </label>

                    @if ($field->field_type === \App\Enums\FieldType::Text)
                        <input type="text" wire:model="customFields.{{ $field->id }}"
                               class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-[#0089CB] focus:outline-none">

                    @elseif ($field->field_type === \App\Enums\FieldType::Textarea)
                        <textarea wire:model="customFields.{{ $field->id }}" rows="4"
                                  class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-[#0089CB] focus:outline-none"></textarea>

                    @elseif ($field->field_type === \App\Enums\FieldType::Select)
                        <select wire:model="customFields.{{ $field->id }}"
                                class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-[#0089CB] focus:outline-none">
                            <option value="">-- Select --</option>
                            @foreach ($field->options ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>

                    @elseif ($field->field_type === \App\Enums\FieldType::Checkbox)
                        <input type="checkbox" wire:model="customFields.{{ $field->id }}"
                               class="h-4 w-4 rounded border-zinc-600 text-[#0089CB]">

                    @elseif ($field->field_type === \App\Enums\FieldType::Date)
                        <input type="date" wire:model="customFields.{{ $field->id }}"
                               class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-[#0089CB] focus:outline-none">

                    @elseif ($field->field_type === \App\Enums\FieldType::File)
                        <input type="file" wire:model="fileUploads.{{ $field->id }}"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full text-sm text-zinc-400 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-700 file:px-4 file:py-2 file:text-sm file:text-zinc-200 hover:file:bg-zinc-600">
                    @endif

                    @error("customFields.{$field->id}")
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    @error("fileUploads.{$field->id}")
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-between">
            <button type="button" wire:click="prevStep"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-semibold text-zinc-300 hover:border-zinc-500">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                <span>Back</span>
            </button>
            <button type="button" wire:click="nextStep"
                    wire:loading.attr="disabled" wire:target="nextStep"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#0089CB] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3] disabled:opacity-60">
                <span>Next</span>
                <x-heroicon-o-arrow-right class="h-4 w-4" />
            </button>
        </div>
    @endif

    @if ($step === 5)
        <h2 class="mb-1 text-xl font-bold">Review your request</h2>
        <p class="mb-6 text-sm text-zinc-400">Check everything looks correct before submitting.</p>

        <div class="space-y-3 rounded-xl border border-zinc-700/60 bg-zinc-800/50 p-5 text-sm">
            <div class="flex gap-3">
                <span class="w-28 shrink-0 text-zinc-400">Office</span>
                <span class="font-medium">{{ $this->selectedOffice?->name }}</span>
            </div>
            <div class="flex gap-3">
                <span class="w-28 shrink-0 text-zinc-400">Category</span>
                <span class="font-medium">{{ $this->selectedCategory?->name }}</span>
            </div>
            <div class="flex gap-3">
                <span class="w-28 shrink-0 text-zinc-400">Service</span>
                <span class="font-medium">{{ $this->selectedServiceType?->name }}</span>
            </div>
            @foreach ($this->fields as $field)
                @if (! empty($customFields[$field->id]))
                    <div class="flex gap-3">
                        <span class="w-28 shrink-0 text-zinc-400">{{ $field->label }}</span>
                        <span class="font-medium">
                            {{ is_array($customFields[$field->id])
                                ? implode(', ', $customFields[$field->id])
                                : $customFields[$field->id] }}
                        </span>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-8 flex justify-between">
            <button type="button" wire:click="prevStep"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-semibold text-zinc-300 hover:border-zinc-500">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                <span>Back</span>
            </button>
            <button type="button" wire:click="submit"
                    wire:loading.attr="disabled"
                    class="rounded-lg bg-[#0089CB] px-8 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3] disabled:opacity-60">
                <span wire:loading.remove wire:target="submit">Submit Request</span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </button>
        </div>
    @endif
</div>
