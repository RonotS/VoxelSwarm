<?php
/**
 * Operator layout — sidebar nav, content area.
 * Receives $content from Response::view()
 */
$pageTitle = $pageTitle ?? 'Dashboard — VoxelSwarm';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <link rel="stylesheet" href="/fonts/inter/inter.css">
  <link rel="stylesheet" href="/build/swarm.css">

  <script>
    (function() {
      var t = localStorage.getItem('swarm-theme') || 'dark';
      document.documentElement.setAttribute('data-theme', t);
      if (t === 'dark') document.documentElement.classList.add('dark');
      else document.documentElement.classList.remove('dark');
    })();
  </script>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 min-h-screen flex font-sans selection:bg-orange-500/30 selection:text-white antialiased">

  <div class="flex min-h-screen w-full">
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-white dark:bg-[#0f0f11] border-r border-zinc-200 dark:border-zinc-800/80 flex flex-col fixed inset-y-0 z-20">
      <div class="px-6 h-16 flex items-center mb-4">
        <a href="/operator" class="flex items-center gap-2.5 text-zinc-900 dark:text-white hover:opacity-80 transition-opacity">
          <svg viewBox="0 0 24 24" class="w-[24px] h-[24px] text-orange-600" xmlns="http://www.w3.org/2000/svg">
            <path class="fill-current opacity-100" d="M12 3L20 7.5L12 12L4 7.5Z" />
            <path class="fill-current opacity-70" d="M4 7.5L12 12L12 21L4 16.5Z" />
            <path class="fill-current opacity-40" d="M20 7.5L12 12L12 21L20 16.5Z" />
          </svg>
          <span class="font-bold tracking-tight text-[16px]">VoxelSwarm</span>
        </a>
      </div>

      <nav class="flex-1 px-3 space-y-1">
        <?php
        $navItem = function($path, $exact, $icon, $label) use ($currentPath) {
          $isActive = $exact ? $currentPath === $path : str_starts_with($currentPath, $path);
          $activeClass = $isActive 
            ? 'bg-zinc-100 dark:bg-zinc-800/50 text-orange-600 dark:text-orange-500 font-medium' 
            : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/50 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium';
          return sprintf(
            '<a href="%s" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm %s">%s%s</a>',
            $path, $activeClass, $icon, $label
          );
        };

        echo $navItem('/operator', true, '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>', 'Dashboard');
        echo $navItem('/operator/instances', false, '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 7V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v3"/></svg>', 'Instances');
        echo $navItem('/operator/templates', false, '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>', 'Templates');
        echo $navItem('/operator/settings', false, '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>', 'Settings');
        ?>
      </nav>

      <div class="p-4 border-t border-zinc-200 dark:border-zinc-800/80 space-y-1">
        <button onclick="toggleTheme()" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/50 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
          <svg id="theme-icon" class="w-[18px] h-[18px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
          <span id="theme-label">Light mode</span>
        </button>
        
        <form method="POST" action="/operator/logout" class="w-full m-0">
          <?= \Swarm\Middleware\Csrf::field() ?>
          <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors">
            <svg class="w-[18px] h-[18px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Log out
          </button>
        </form>
        <div class="px-3 pt-2 text-[11px] font-medium text-zinc-400 dark:text-zinc-600">v<?= SWARM_VERSION ?></div>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 ml-64 p-8 md:p-12 max-w-[1200px] relative">
      <?= $content ?>
    </main>
  </div>

  <!-- Global Toast Container -->
  <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

  <script>
    function showToast(message, type = 'success') {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');
      
      const icons = {
          success: '<svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
          error: '<svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
          info: '<svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
      };

      toast.className = `transform transition-all duration-300 translate-y-8 opacity-0 flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-[0_4px_12px_rgba(0,0,0,0.05),_0_1px_2px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] rounded-xl text-sm font-medium text-zinc-900 dark:text-zinc-100 pointer-events-auto`;
      toast.innerHTML = `${icons[type] || icons.info} <span>${message}</span>`;
      
      container.appendChild(toast);
      
      // Animate in
      requestAnimationFrame(() => {
        toast.classList.remove('translate-y-8', 'opacity-0');
      });

      // Animate out
      setTimeout(() => {
        toast.classList.add('translate-y-8', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
      }, 4000);
    }

    function toggleTheme() {
      const html = document.documentElement;
      const current = html.getAttribute('data-theme') || 'dark';
      const next = current === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-theme', next);
      if (next === 'dark') {
          html.classList.add('dark');
      } else {
          html.classList.remove('dark');
      }
      localStorage.setItem('swarm-theme', next);
      updateThemeUI(next);
      showToast('Theme updated to ' + next + ' mode', 'info');
    }
    
    function updateThemeUI(theme) {
      const label = document.getElementById('theme-label');
      const icon = document.getElementById('theme-icon');
      
      if (label) label.textContent = theme === 'dark' ? 'Light mode' : 'Dark mode';
      if (icon) {
        if (theme === 'dark') {
            // Sun icon for switching to light
            icon.innerHTML = '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>';
        } else {
            // Moon icon for switching to dark
            icon.innerHTML = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
        }
      }
    }
    updateThemeUI(document.documentElement.getAttribute('data-theme') || 'dark');
  </script>
</body>
</html>
