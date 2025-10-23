<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  header('Location: /login/');
  exit;
}

$user_id = $_SESSION['user_id'];
$show_id = $_SESSION['active_show'];

// Permissions
$is_manager = in_array($_SESSION['role'] ?? '', ['director','manager','admin']);

// How many users are in this show? (used for “Everyone Called” check)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM show_users WHERE show_id = ?");
$stmt->execute([$show_id]);
$totalShowUsers = (int)$stmt->fetchColumn();

// Fetch rehearsals (and whether current user is called, and attendee count)
// Determine filter
$filter = $_GET['filter'] ?? 'all';
$where = "r.show_id = ?";
$params = [$show_id];

// Adjust query based on filter
switch ($filter) {
  case 'mine':
    $where .= " AND EXISTS(SELECT 1 FROM rehearsal_attendees ra WHERE ra.rehearsal_id = r.id AND ra.user_id = ?)";
    $params[] = $user_id;
    break;
  case 'upcoming':
    $where .= " AND r.date >= CURDATE()";
    break;
  case 'past':
    $where .= " AND r.date < CURDATE()";
    break;
  default:
    break;
}

// Fetch rehearsals
$stmt = $pdo->prepare("
  SELECT
    r.*,
    EXISTS(SELECT 1 FROM rehearsal_attendees ra WHERE ra.rehearsal_id = r.id AND ra.user_id = ?) AS is_attending,
    (SELECT COUNT(*) FROM rehearsal_attendees ra2 WHERE ra2.rehearsal_id = r.id) AS attendee_count,
    (SELECT GROUP_CONCAT(DISTINCT SUBSTRING_INDEX(u.full_name, ' ', 1) ORDER BY u.full_name SEPARATOR ', ')
     FROM rehearsal_attendees ra3
     JOIN users u ON ra3.user_id = u.id
     WHERE ra3.rehearsal_id = r.id
    ) AS attendee_first_names
  FROM rehearsals r
  WHERE $where
  ORDER BY r.date ASC, r.start_time ASC
");
$stmt->execute(array_merge([$user_id], $params));
$rehearsals = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Colours from config
$accent = htmlspecialchars($config['border_colour'] ?? '#ef4444');           // border accent for “mine”
$button = htmlspecialchars($config['button_colour'] ?? '#ef4444');           // calendar highlight
$buttonHover = htmlspecialchars($config['button_hover_colour'] ?? '#dc2626');
$textColour = htmlspecialchars($config['text_colour'] ?? '#111827');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rehearsals | <?= htmlspecialchars($config['site_title']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- FullCalendar (calendar view) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold text-[<?= $textColour ?>]">🎭 Rehearsal Schedule</h1>
      <!-- Filter Chips -->
      <div class="flex flex-wrap gap-2 mt-4 mb-6">
        <?php
        $filters = [
          'all' => 'All',
          'mine' => 'Only Mine',
          'upcoming' => 'Upcoming',
          'past' => 'Past'
        ];
        foreach ($filters as $key => $label) {
          $active = $filter === $key;
          $base = "px-3 py-1 rounded-full border text-sm font-medium transition";
          $classes = $active
            ? "bg-[{$config['button_colour']}] text-white border-[{$config['button_colour']}]"
            : "border-gray-300 text-gray-700 hover:bg-gray-100";
          echo "<a href='?filter=$key' class='$base $classes'>$label</a>";
        }
        ?>
      </div>
      <div class="flex gap-2">
        <button id="toggleViewBtn"
          class="bg-[<?= $button ?>] hover:bg-[<?= $buttonHover ?>] text-white px-4 py-2 rounded shadow transition">
          Switch to Calendar View
        </button>
        <?php if ($is_manager): ?>
          <a href="/rehearsals/add_rehearsal.php"
             class="bg-[<?= $button ?>] hover:bg-[<?= $buttonHover ?>] text-white px-4 py-2 rounded shadow transition">
            + Add Rehearsal
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- List View -->
    <section id="listView">
      <?php if (count($rehearsals) === 0): ?>
        <div class="text-center py-10 text-gray-500 italic">
          No rehearsals scheduled yet.
        </div>
      <?php else: ?>
        <div class="grid gap-4 md:grid-cols-2">
          <?php foreach ($rehearsals as $r): ?>
            <?php
              $dateStr = date('D, M j', strtotime($r['date']));
              // times in 12-hr like 6:00 PM – 8:00 PM
              $start = date('g:i A', strtotime($r['start_time']));
              $end   = date('g:i A', strtotime($r['end_time']));
              $everyone = ((int)$r['attendee_count'] === $totalShowUsers && $totalShowUsers > 0);
              $border = $r['is_attending'] ? $accent : '#e5e7eb'; // gray-200 for others
            ?>
            <div class="bg-white rounded-xl p-5 shadow-md border-l-4 flex flex-col gap-1 hover:shadow-lg transition"
                 style="border-left-color: <?= $border ?>;">
              <div class="text-sm text-gray-600"><?= htmlspecialchars($dateStr) ?> — <?= htmlspecialchars($start) ?>–<?= htmlspecialchars($end) ?></div>
              <h3 class="text-xl font-bold"><?= htmlspecialchars($r['title']) ?></h3>
              <?php if (!empty($r['location'])): ?>
                <div class="text-gray-700 text-sm">📍 <?= htmlspecialchars($r['location']) ?></div>
              <?php endif; ?>

              <div class="text-gray-700 text-sm">
                <?php if ($everyone): ?>
                  Everyone Called
                <?php else: ?>
                  <?php
                    $names = trim((string)($r['attendee_first_names'] ?? ''));
                    if ($names === '') {
                        echo 'No attendees';
                    } else {
                        echo htmlspecialchars($names);
                    }
                  ?>
                <?php endif; ?>
              </div>

              <?php if (!empty($r['notes'])): ?>
                <div class="text-gray-600 text-sm italic">“<?= htmlspecialchars($r['notes']) ?>”</div>
              <?php endif; ?>

              <?php if ($is_manager): ?>
                <div class="flex gap-4 mt-2 text-sm">
                  <a href="/rehearsals/edit_rehearsal.php?id=<?= $r['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                  <a href="/rehearsals/delete_rehearsal.php?id=<?= $r['id'] ?>" class="text-red-600 hover:underline"
                     onclick="return confirm('Delete this rehearsal?');">Delete</a>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Calendar View -->
    <section id="calendarView" class="hidden">
      <div id="calendar" class="bg-white rounded-xl p-4 shadow"></div>
    </section>
  </main>

  <script>
    const buttonColour = '<?= $button ?>';
    const buttonHover = '<?= $buttonHover ?>';

    const listView = document.getElementById('listView');
    const calendarView = document.getElementById('calendarView');
    const toggleBtn = document.getElementById('toggleViewBtn');
    let calendarShown = false;
    let calendar;

    function initCalendar() {
      const calendarEl = document.getElementById('calendar');
      calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        eventSources: [
          {
            url: '/rehearsals/list/',
            method: 'GET',
            failure: () => alert('Failed to load rehearsals.')
          }
        ],
        eventDidMount: (info) => {
          if (info.event.extendedProps.attending) {
            info.el.style.backgroundColor = buttonColour;
            info.el.style.borderColor = buttonColour;
            info.el.style.color = 'white';
          }
          // Tooltip title with location/notes
          const loc = info.event.extendedProps.location || '';
          const notes = info.event.extendedProps.notes || '';
          if (loc || notes) {
            info.el.title = (loc ? ('Location: ' + loc) : '') + (loc && notes ? '\n' : '') + (notes ? ('Notes: ' + notes) : '');
          }
        }
      });
      calendar.render();
    }

    toggleBtn.addEventListener('click', () => {
      calendarShown = !calendarShown;
      if (calendarShown) {
        listView.classList.add('hidden');
        calendarView.classList.remove('hidden');
        toggleBtn.textContent = 'Switch to List View';
        if (!calendar) initCalendar();
        else calendar.updateSize();
      } else {
        calendarView.classList.add('hidden');
        listView.classList.remove('hidden');
        toggleBtn.textContent = 'Switch to Calendar View';
      }
    });
  </script>
</body>
</html>
