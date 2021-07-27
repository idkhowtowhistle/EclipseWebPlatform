<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
  users::requireLogin(1);

  $nameCache = [];

  $ChatColors =
  [
      "#C4281C", // Bright red
      "#0D69AC", // Bright blue
      "#27462D", // Earth green
      "#6B327C", // Bright violet
      "#DA8541", // Bright orange
      "#F5CD30", // Bright yellow
      "#E8BAC8", // Light reddish violet
      "#D7C59A"  // Brick yellow
  ];

  function ComputeChatColor($name)
  {
    global $ChatColors;

    $namelen = strlen($name);
    $value = 0;
    $namevalue = 0;
      
    for ($index = 1; $index <= $namelen; $index++)
    {
      $cValue = ord(substr($name, $index-1, 1));
      $reverseIndex = $namelen - $index + 1;
        
      if ($namelen % 2 == 1) $reverseIndex = $reverseIndex - 1;
      if ($reverseIndex % 4 >= 2) $cValue = -$cValue  ;
        
      $value = $value + $cValue;
    }

    while ($value < 0) $value += 8;
    $value = $value % 8;

    return $ChatColors[$value];
  }

  function constructChatLogs($report)
  {
    $chatlogs = json_decode($report->messages);
    if(!count($chatlogs)) return "No chat logs were recorded";

    $output = "";

    foreach ($chatlogs as $log)
    {
      if (!isset($nameCache[$log->UserID]))
        $nameCache[$log->UserID] = users::getUserNameFromUid($log->UserID);

      $username = $nameCache[$log->UserID];
      $message = htmlspecialchars(htmlspecialchars($log->Message));
      
      $output .= sprintf("<span style='color:%s'>%s</span>: %s<br>", ComputeChatColor($username), $username, $message);
    }

    return $output;
  }

  $reports = db::run("SELECT * FROM reports ORDER BY id DESC");

  pageBuilder::buildHeader();
?>
<div class="app py-4 container">
  <h3 class="mb-4 font-weight-normal">User Reports</h3>
  <table class="table table-hover" id="auditlist">
    <thead class="table-bordered bg-light">
      <tr>
        <th class="font-weight-normal py-2" scope="col">Date</th>
        <th class="font-weight-normal py-2" scope="col">Reporter</th>
        <th class="font-weight-normal py-2" scope="col">Abuser</th>
        <th class="font-weight-normal py-2" scope="col">Reason</th>
        <th class="font-weight-normal py-2" scope="col">Description</th>
        <th class="font-weight-normal py-2" scope="col">Chat logs</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($report = $reports->fetch(PDO::FETCH_OBJ)) { ?>
      <tr>
        <td><?=date('j F Y \a\t g:i A', $report->time)?></td>
        <td><a href="/users?id=<?=$report->reporterID?>"><?=users::getUserNameFromUid($report->reporterID)?></a></td>
        <td><a href="/users?id=<?=$report->abuserID?>"><?=users::getUserNameFromUid($report->abuserID)?></a></td>
        <td><?=htmlspecialchars($report->abuse)?></td>
        <td><?=htmlspecialchars($report->description) ?: "No description available"?></td>
        <td><button type="button" class="btn btn-primary btn-sm btn-block" data-logs="<?=constructChatLogs($report)?>" onclick="showModal('Chat logs', $(this).attr('data-logs'), [{'class':'btn btn-outline-success', 'isDismissButton':true, 'text':'Close'}]);">View</button></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<?php pageBuilder::buildFooter(); ?>

