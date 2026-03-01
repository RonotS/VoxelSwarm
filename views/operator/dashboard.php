<?php
/**
 * Operator dashboard — summary cards + recent activity.
 */
$pageTitle = 'Dashboard — VoxelSwarm';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Dashboard</h1>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Overview of your VoxelSwarm cluster.</p>
  </div>
  <button onclick="document.getElementById('modal-demo').classList.remove('hidden'); document.getElementById('modal-demo').classList.add('flex')" 
          class="sw-btn-primary">
    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Demo Instance
  </button>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
  <?php
  $renderCard = function($label, $value, $valueColorClass = '') {
    return '
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800/80 rounded-xl p-5 shadow-sm dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] flex flex-col justify-between">
      <div class="text-[13px] font-medium text-zinc-500 dark:text-zinc-400">'.$label.'</div>
      <div class="text-3xl font-bold tracking-tight mt-2 '.$valueColorClass.'">'.$value.'</div>
    </div>';
  };
  
  echo $renderCard('Total Instances', $counts['total'], 'text-zinc-900 dark:text-white');
  echo $renderCard('Active', $counts['active'], 'text-green-600');
  echo $renderCard('Paused', $counts['paused'], $counts['paused'] > 0 ? 'text-amber-600' : 'text-zinc-900 dark:text-white');
  echo $renderCard('Storage Used', $storageUsed, 'text-zinc-900 dark:text-white text-[24px]');
  ?>
</div>

<!-- Activity Log -->
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800/80 rounded-xl shadow-sm dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] overflow-hidden">
  <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800/80 bg-zinc-50/50 dark:bg-zinc-800/20">
    <h2 class="text-base font-semibold tracking-tight text-zinc-900 dark:text-white">Recent Activity</h2>
  </div>
  
  <?php if (empty($recentLogs)): ?>
    <div class="p-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
      No provisioning activity yet. Create a demo instance to get started.
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm whitespace-nowrap">
        <thead>
          <tr class="bg-zinc-50/50 dark:bg-zinc-800/20 border-b border-zinc-100 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400">
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px]">Instance</th>
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px]">Step</th>
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px]">Status</th>
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px]">Duration</th>
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px] text-right">Time</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
          <?php foreach ($recentLogs as $log): ?>
            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
              <td class="px-5 py-3">
                <a href="/operator/instances/<?= $log['instance_id'] ?>" class="font-medium text-zinc-900 dark:text-white hover:text-orange-600 dark:hover:text-orange-500 transition-colors group">
                  <?= htmlspecialchars($log['slug'] ?? '—') ?>
                  <span class="inline-block ml-1 opacity-0 group-hover:opacity-100 transition-opacity">→</span>
                </a>
              </td>
              <td class="px-5 py-3 text-zinc-500 dark:text-zinc-400"><?= htmlspecialchars($log['step']) ?></td>
              <td class="px-5 py-3">
                <?php
                  $badgeClass = match($log['status']) {
                    'completed' => 'bg-green-100/50 text-green-700 dark:bg-green-500/10 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/20',
                    'failed'    => 'bg-red-100/50 text-red-700 dark:bg-red-500/10 dark:text-red-400 ring-1 ring-inset ring-red-600/10 dark:ring-red-500/20',
                    'started'   => 'bg-orange-100/50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400 ring-1 ring-inset ring-orange-600/20 dark:ring-orange-500/20 animate-pulse',
                    default     => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 ring-1 ring-inset ring-zinc-500/20 dark:ring-zinc-400/20',
                  };
                ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold tracking-wide <?= $badgeClass ?>">
                  <?= strtoupper($log['status']) ?>
                </span>
              </td>
              <td class="px-5 py-3 text-zinc-500 dark:text-zinc-400 font-[tabular-nums]">
                <?= $log['duration_ms'] ? $log['duration_ms'] . 'ms' : '—' ?>
              </td>
              <td class="px-5 py-3 text-zinc-500 dark:text-zinc-400 text-xs text-right">
                <?= date('M j, H:i', strtotime($log['created_at'])) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- New Demo Instance Modal -->
<div id="modal-demo" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/50 backdrop-blur-sm transition-opacity">
  <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl rounded-2xl w-full max-w-md overflow-hidden transform transition-all dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
    <div class="px-6 py-5 border-b border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between">
      <h3 class="text-base font-semibold tracking-tight text-zinc-900 dark:text-white">New Demo Instance</h3>
      <button type="button" onclick="document.getElementById('modal-demo').classList.remove('flex'); document.getElementById('modal-demo').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300 transition-colors">
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    
    <div class="px-6 py-5">
      <form id="form-demo" class="space-y-4">
        <div>
          <label for="demo-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Business Name</label>
          <input type="text" id="demo-name" name="name" placeholder="e.g. Sample Bakery" required 
                 class="block w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-white placeholder-zinc-400 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 transition-shadow">
        </div>
        
        <div class="pt-2 flex gap-3">
          <button type="button" onclick="document.getElementById('modal-demo').classList.remove('flex'); document.getElementById('modal-demo').classList.add('hidden')" 
                  class="flex-1 sw-btn-secondary">
            Cancel
          </button>
          <button type="submit" 
                  class="flex-1 sw-btn-primary">
            Create Demo
          </button>
        </div>
      </form>
      <div id="demo-result" class="hidden mt-4 p-3 rounded-lg text-sm font-medium"></div>
    </div>
  </div>
</div>

<script>
  // Close modal on outside click
  document.getElementById('modal-demo').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('flex');
        this.classList.add('hidden');
    }
  });

  // Demo instance form
  document.getElementById('form-demo').addEventListener('submit', function(e) {
    e.preventDefault();
    const name = document.getElementById('demo-name').value.trim();
    if (!name) return;

    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating...';

    const csrf = '<?= \Swarm\Middleware\Csrf::token() ?>';

    fetch('/operator/instances', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf },
      body: '_token=' + encodeURIComponent(csrf) + '&name=' + encodeURIComponent(name)
    })
    .then(r => r.json())
    .then(data => {
      const result = document.getElementById('demo-result');
      result.classList.remove('hidden');
      if (data.error) {
        result.className = 'mt-4 p-3 rounded-lg text-sm font-medium bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20';
        result.textContent = data.error;
        btn.disabled = false;
        btn.innerHTML = originalText;
      } else {
        result.className = 'mt-4 p-3 rounded-lg text-sm font-medium bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-500/20';
        result.textContent = 'Instance "' + data.slug + '" provisioning started!';
        setTimeout(() => location.reload(), 1500);
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = originalText;
    });
  });
</script>
