export type ThemeName =
    | 'surigao'
    | 'sunburst'
    | 'forest'
    | 'midnight'
    | 'aurora'
    | 'ember'
    | 'slate'
    | 'coral';

export type ThemeOption = {
    value: ThemeName;
    label: string;
    swatch: string; // a representative color for the switcher UI
};

export const themes: ThemeOption[] = [
    { value: 'surigao', label: 'Surigao', swatch: 'hsl(189 82% 30%)' },
    { value: 'sunburst', label: 'Sunburst', swatch: 'hsl(14 85% 55%)' },
    { value: 'forest', label: 'Forest Canopy', swatch: 'hsl(150 55% 28%)' },
    { value: 'midnight', label: 'Midnight Lagoon', swatch: 'hsl(245 60% 45%)' },
    { value: 'aurora', label: 'Aurora', swatch: 'hsl(175 70% 38%)' },
    { value: 'ember', label: 'Ember Coast', swatch: 'hsl(350 75% 52%)' },
    { value: 'slate', label: 'Slate Office', swatch: 'hsl(220 20% 30%)' },
    { value: 'coral', label: 'Coral Reef', swatch: 'hsl(6 78% 58%)' },
];

export const DEFAULT_THEME: ThemeName = 'surigao';