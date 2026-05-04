<div class="empty {{ $empty_classes ?? '' }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="mb-3" viewBox="0 0 200 160" width="200" height="160">
        <!-- Empty box/container -->
        <rect x="20" y="40" width="160" height="100" rx="8" ry="8" fill="none" stroke="#e6e6e6" stroke-width="2" stroke-dasharray="5,5"/>

        <!-- Inner content area with subtle pattern -->
        <rect x="30" y="50" width="140" height="80" rx="4" ry="4" fill="#f8f9fa" opacity="0.5"/>

        <!-- Dotted lines representing missing content -->
        <line x1="40" y1="70" x2="160" y2="70" stroke="#dee2e6" stroke-width="1" stroke-dasharray="2,4"/>
        <line x1="40" y1="90" x2="140" y2="90" stroke="#dee2e6" stroke-width="1" stroke-dasharray="2,4"/>
        <line x1="40" y1="110" x2="120" y2="110" stroke="#dee2e6" stroke-width="1" stroke-dasharray="2,4"/>

        <!-- Magnifying glass icon -->
        <circle cx="100" cy="25" r="15" fill="none" stroke="{{ @$themeSettings->system_admin->colors->primary_color }}" stroke-width="2"/>
        <circle cx="100" cy="25" r="8" fill="none" stroke="{{ @$themeSettings->system_admin->colors->primary_color }}" stroke-width="1.5"/>
        <line x1="112" y1="37" x2="125" y2="50" stroke="{{ @$themeSettings->system_admin->colors->primary_color }}" stroke-width="2" stroke-linecap="round"/>

        <!-- Small decorative elements -->
        <circle cx="50" cy="75" r="2" fill="{{ @$themeSettings->system_admin->colors->primary_color }}" opacity="0.3"/>
        <circle cx="70" cy="95" r="1.5" fill="{{ @$themeSettings->system_admin->colors->primary_color }}" opacity="0.3"/>
        <circle cx="150" cy="85" r="2" fill="{{ @$themeSettings->system_admin->colors->primary_color }}" opacity="0.3"/>
    </svg>
    <h1 class="mt-3">{{ translate('No Results Found!') }}</h1>
    <p>{{ translate('It seems that the section is empty or your') }}<br>{{ translate('search didn\'t fetch any results') }}
    </p>
</div>


















