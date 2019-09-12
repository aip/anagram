<!doctype html>
<html lang="en">

<head>
    <title>Anagrams</title>
</head>

<body>

<form action="" method="post">
    <p>
    <label for="anagram">Input word: </label>
    <input type="text" name="anagram" size="32" value="<?php if(array_key_exists("anagram", $_POST)) echo $_POST['anagram']; else echo "Listen"?>"/>
    </p>
    <p>
    <label for="dictionary">Select dictionary:</label>
    <select required name="dictionary">
        <option
                <?php if(array_key_exists("dictionary", $_POST) and $_POST["dictionary"] == "aspell_english") echo "selected"; ?>
                value="aspell_english">The Aspell English Dictionary (use in PHP) ~ 123,000 words</option>
        <option
                <?php if(array_key_exists("dictionary", $_POST) and $_POST["dictionary"] == "words_alpha") echo "selected"; ?>
                value="words_alpha">Other English Dictionary ~ 370,000 words</option>
    </select>
    </p>
    <p><input type="submit" value="Get anagrams"/></p>
</form>


<?php

if (array_key_exists("dictionary", $_POST) and $_POST["dictionary"] == "words_alpha") {
    $dictionary = "words_alpha"; // 370,000 words
} else {
    $dictionary = "aspell_english"; // 123,000 words
}

if (array_key_exists("anagram", $_POST)) {
    $anagram = htmlspecialchars($_POST['anagram']);
} else {
    // $anagram = "stop";
    // $anagram = "abbreviation";
    $anagram = "listen";
}

$database = "sqlite:" . __DIR__ . "/$dictionary.sqlite";
$dictionary_path = __DIR__ . "/$dictionary.dict";

try {
    $dbh = new PDO($database);

    //Create the cached table if not exists
    if (!$dbh->query("SELECT name FROM sqlite_master WHERE type='table' AND name='word'")->fetch()) {
        $dbh->exec("CREATE TABLE IF NOT EXISTS `word` (
            `spelling` VARCHAR(32) NOT NULL UNIQUE,
            `characters` VARCHAR(32) NOT NULL
        )");
        $stmt = $dbh->prepare("INSERT INTO `word` (spelling, characters) VALUES(:spelling, :characters)");

        try {
            $dbh->beginTransaction();
            if ($file = fopen($dictionary_path, "r")) {
                while (!feof($file)) {
                    $line = fgets($file);
                    $word = strtolower(trim($line));
                    $characters = str_split($word);
                    sort($characters);
                    $stmt->execute(array($word, implode($characters)));
                }
                fclose($file);
            }
            $dbh->commit();
            $dbh->exec("CREATE INDEX `characters_index` ON `word` (`characters`)");

        } catch (PDOExecption $e) {
            $dbh->rollback();
            print "Error!: " . $e->getMessage() . "<br />";
        }
    }

    //Select anagrams of the word
    $stmt = $dbh->prepare("SELECT * FROM `word` WHERE characters=:characters");
    $word = strtolower(trim($anagram));
    $characters = str_split($word);
    sort($characters);
    $stmt->bindValue(':characters', implode($characters), PDO::PARAM_STR);
    $stmt->execute();

    //Show anagrams of the word
    echo "<ol>\n";
    foreach ($stmt->fetchall() as $row) {
        echo "<li><strong>" . ucwords($row['spelling']) . "</strong></li>\n";
    }
    echo "</ol>\n";

    $dbh = null;
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br />";
    die();
}

?>

</body>

</html>