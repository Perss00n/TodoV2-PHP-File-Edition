<?php

$filnamn = "todos.txt";

/* Här kollar jag att filen existerar och använder en ternary för att säga, OM den gör det så läs in hela filen som en array där varje rad blir ett eget element med file(), annars skapa en tom array. Jag säger också till php att läsa listan UTAN tomma rader och UTAN att skapa extra rader*/
$innehåll = file_exists($filnamn) ? file($filnamn, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

/* Lägger till en ny uppgift, här kollar jag så att det finns ett värde för min globala POST array men INTE vid en uppdatering för det hanterar
jag separat här nedanför*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["text"]) && !isset($_POST["update"])) {
  $text = trim($_POST["text"]);


  /* Ingen snygg lösning men det hindrar iaf att formuläret ballar ur om texten är för lång, i en perfekt värld hade jag sparat alla fel i en egen array och
  printat ut dom under titeln med en foreach loop eller något liknande */
  if (strlen($text) > 30) {
    die("Max 30 tecken är tillåtna!");
  }

  // Om inte värdet är tomt, kör if satsen, annars skickas vi tillbaka till startsidan för todo appen utan att något händer
  if (!empty($text)) {

    /* Ser till så att det inte läggs till en extra rad efter sista posten genom att kolla om filen är tom eller inte, om den inte är det så lägger jag till en radbrytning innan jag lägger till den nya posten
    jag säger till php att jag vill LÄGGA TILL datan i arrayen via append och inte skriva över den */
    file_put_contents($filnamn, ($innehåll ? "\n" : "") . $text, FILE_APPEND);
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

// Tar bort en uppgift
if (isset($_GET["remove"])) {

  // Jag talar om explicit att detta värdet ska vara en int och inget annat, detta gör koden säkrare
  $removeIndex = (int) $_GET["remove"];

  // Om det finns en uppgift på det indexet jag vill ta bort så körs if satsen, annars skickas vi tillbaka till startsidan för todo appen utan att något händer
  if (isset($innehåll[$removeIndex])) {
    // Tar bort elementet på det hämtade indexet
    unset($innehåll[$removeIndex]);
    // Omindexerar arrayen
    $innehåll = array_values($innehåll);
    //Efter att jag har indexerat om min array så skriver jag tillbaka den nya uppdaterade arrayen till filen, varje ny rad blir ett eget element
    file_put_contents($filnamn, implode("\n", $innehåll));
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

// Redigerar en uppgift, här kollar jag att jag har ett update och index värde i min POST array
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"]) && isset($_POST["index"])) {
  // Jag talar om explicit att detta värdet ska vara en int och inget annat, detta gör koden säkrare
  $editIndex = (int) $_POST["index"];
  $nyText = trim($_POST["text"]);

  if (strlen($nyText) > 30) {
    die("Max 30 tecken är tillåtna!");
  }

  // Om inte värdet är tomt och om det finns en uppgift på det indexet jag vill redigera så körs if satsen, annars skickas vi tillbaka till startsidan för todo appen utan att något händer
  if (!empty($nyText) && isset($innehåll[$editIndex])) {
    // Uppdaterar värdet i arrayen på det hämtade indexet
    $innehåll[$editIndex] = $nyText;
    // Skriver tillbaka det uppdaterade värdet till filen igen
    file_put_contents($filnamn, implode("\n", $innehåll));
  }

  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

/*Ändrar om en uppgift är markerad som klar eller inte, jag valde att lägga till ett * i början av strängen för att markera att uppgiften är klar och sedan ta bort det om den inte är det.
Jag valde att göra så för att jag inte ville ta bort uppgiften helt ur listan om den var klar, utan bara markera den som klar, detta gör att jag enkelt kan kolla efter * i början av strängen
i min html och styra hur uppgiften ska visas*/
if (isset($_GET["toggle"])) {
  // Jag talar om explicit att detta värdet ska vara en int och inget annat, detta gör koden säkrare
  $toggleIndex = (int)$_GET["toggle"];
  // Om det finns en uppgift på det indexet jag vill redigera så körs if satsen, annars skickas vi tillbaka till startsidan för todo appen utan att något händer
  if (isset($innehåll[$toggleIndex])) {
    // Om det finns ett * på index 0 i strängen så tar jag bort det genom att använda substr och ta bort det första tecknet i strängen, annars lägger jag till ett * på index 0
    if (strpos($innehåll[$toggleIndex], "*") === 0) {
      $innehåll[$toggleIndex] = substr($innehåll[$toggleIndex], 1);
    } else {
      $innehåll[$toggleIndex] = "*" . $innehåll[$toggleIndex];
    }
    // Skriver tillbaka det uppdaterade värdet till filen igen
    file_put_contents($filnamn, implode("\n", $innehåll));
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

?>

<!DOCTYPE html>
<html lang="sv">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <!-- Här länkar jag in font awesome för att använda deras ikoner -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Marcus ToDo App</title>
</head>

<body>
  <h1 class="title">MARCUS TODO APP</h1>

  <div class="input-container">
    <form method="POST" action="">
      <input type="text" name="text" class="todo-input" placeholder="Ange en ny todo och tryck på enter">
      <button type="submit" class="add-button">LÄGG TILL</button>
    </form>
  </div>

  <div class="todo-list">
    <?php foreach ($innehåll as $i => $uppgift): ?>
      <!-- Här kollar jag om uppgiften är checkad eller inte genom att kolla om det finns ett * på index 0 i strängen -->
      <?php $isChecked = strpos($uppgift, "*") === 0; ?>
      <!-- Här skapar jag en div för varje uppgift och kollar om den är checkad eller inte, om den är det så lägger jag till en klass som heter checked -->
      <div class="todo-item <?= $isChecked ? 'checked' : '' ?>">
        <!-- Här skapar jag en checkbox för varje uppgift och kollar om den är checkad eller inte, om den är det så lägger jag till checked attributet -->
        <input type="checkbox"
          class="todo-checkbox" <?= $isChecked ? 'checked' : '' ?>
          onChange="window.location.href='todo.php?toggle=<?= $i ?>'">
        <!-- Här kollar jag om jag är i edit mode genom att kolla om jag har ett edit värde i min GET array och om det är samma som indexet för uppgiften jag är på -->
        <?php if (isset($_GET["edit"]) && (int)$_GET["edit"] === $i): ?>
          <form method="POST" action="">
            <input type="hidden" name="index" value="<?= $i ?>">
            <input type="text" name="text" value="<?= htmlspecialchars($isChecked ? substr($uppgift, 1) : $uppgift) ?>"> <!-- Här kollar jag om uppgiften är checkad eller inte, om den är det så tar jag bort * från början av strängen innan den visas -->
            <button type="submit" name="update">Spara</button>
          </form>
        <?php else: ?>
          <!-- Här kollar jag om uppgiften är checkad eller inte, om den är det så tar jag bort * från början av strängen innan den visas -->
          <?= htmlspecialchars($isChecked ? substr($uppgift, 1) : $uppgift) ?>
          <div class="actions">
            <button class="edit-button">
              <a href="todo.php?edit=<?= $i ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </a>
            </button>
            <button class="delete-button">
              <!-- Här skapar jag en länk för att ta bort en uppgift och skickar med indexet för uppgiften jag vill ta bort -->
              <a href="todo.php?remove=<?= $i ?>" onclick="return confirm('Är du säker på att du vill ta bort denna todo?')">
                <i class="fa-solid fa-trash"></i>
              </a>
            </button>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</body>

</html>