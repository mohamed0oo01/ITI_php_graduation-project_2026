<div class="mb-4">
    <label for="title" class="block text-sm font-medium text-brown mb-1">Job Title</label>
    <input type="text" name="title" id="title" value="{{ old('title', $job->title) }}" required
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
    @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mb-4">
    <label for="description" class="block text-sm font-medium text-brown mb-1">Description</label>
    <textarea name="description" id="description" rows="5" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">{{ old('description', $job->description) }}</textarea>
    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mb-4">
    <label for="required_skills" class="block text-sm font-medium text-brown mb-1">Required Skills</label>
    <input type="text" name="required_skills" id="required_skills" value="{{ old('required_skills', $job->required_skills) }}"
           placeholder="e.g. PHP, Laravel, MySQL" required
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
    @error('required_skills')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mb-4">
    <label for="category" class="block text-sm font-medium text-brown mb-1">Category</label>
    <input type="text" name="category" id="category" value="{{ old('category', $job->category) }}" required
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
    @error('category')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mb-4">
    <label for="location" class="block text-sm font-medium text-brown mb-1">Location</label>
    <input type="text" name="location" id="location" value="{{ old('location', $job->location) }}" required
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
    @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mb-4">
    <label for="work_type" class="block text-sm font-medium text-brown mb-1">Work Type</label>
    <select name="work_type" id="work_type" required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
        <option value="" @disabled(!old('work_type') && !$job->work_type)>Select work type...</option>
        @foreach (['Remote', 'On-site', 'Hybrid'] as $type)
            <option value="{{ $type }}" @selected(old('work_type', $job->work_type) === $type)>{{ $type }}</option>
        @endforeach
    </select>
    @error('work_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mb-4">
    <label for="salary" class="block text-sm font-medium text-brown mb-1">Salary (EGP)</label>
    <input type="number" name="salary" id="salary" value="{{ old('salary', $job->salary) }}" min="0" step="0.01" required
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
    @error('salary')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mb-6">
    <label for="application_deadline" class="block text-sm font-medium text-brown mb-1">Application Deadline</label>
    <input type="date" name="application_deadline" id="application_deadline"
           value="{{ old('application_deadline', $job->application_deadline?->format('Y-m-d')) }}" required
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
    @error('application_deadline')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>