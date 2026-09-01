const fs = require('fs');
const content = fs.readFileSync('resources/views/filament/pages/overview-dashboard.blade.php', 'utf8');
const lines = content.split(/\r?\n/);

// _dash_styles.blade.php
const styles = '@once\n' + lines.slice(30, 542).join('\n') + '\n@endonce';
fs.writeFileSync('resources/views/filament/pages/_partials/_dash_styles.blade.php', styles);

// _dash_header.blade.php
const header = lines.slice(545, 570).join('\n');
fs.writeFileSync('resources/views/filament/pages/_partials/_dash_header.blade.php', header);

// _dash_top_row.blade.php
const topRow = lines.slice(570, 643).join('\n');
fs.writeFileSync('resources/views/filament/pages/_partials/_dash_top_row.blade.php', topRow);

// _dash_stats.blade.php
const stats = lines.slice(643, 676).join('\n');
fs.writeFileSync('resources/views/filament/pages/_partials/_dash_stats.blade.php', stats);

// _dash_schedule.blade.php
const schedule = lines.slice(676, 722).join('\n');
fs.writeFileSync('resources/views/filament/pages/_partials/_dash_schedule.blade.php', schedule);

// _dash_charts.blade.php
const charts = lines.slice(722, 744).join('\n');
fs.writeFileSync('resources/views/filament/pages/_partials/_dash_charts.blade.php', charts);

console.log('All partials extracted successfully!');