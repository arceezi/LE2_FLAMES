<?php
/**
 * ============================================================
 *  Your Name: ________________________________
 *  Date Created: _____________________________
 *  Problem Description:
 *    Laboratory Exercise 2 - FLAMES Form
 *    Create a web page that accepts two names and their birthdays,
 *    determines each person's zodiac sign, and computes the FLAMES
 *    compatibility result based on common letters in both names.
 * ============================================================
 *
**/

declare(strict_types=1);

// -----------------------------
// Helper: sanitize & normalize a name for letter-based comparison
// -----------------------------
function normalize_name(string $name): string
{
    // Keep letters only; remove spaces, punctuation, numbers, etc.
    $lettersOnly = preg_replace('/[^a-zA-Z]/', '', $name) ?? '';
    return strtoupper($lettersOnly);
}

// -----------------------------
// Helper: count letter frequencies for A-Z
// -----------------------------
function letter_frequencies(string $normalizedName): array
{
    $freq = array_fill(0, 26, 0);
    $len = strlen($normalizedName);

    for ($i = 0; $i < $len; $i++) {
        $ch = $normalizedName[$i];
        $idx = ord($ch) - ord('A');
        if ($idx >= 0 && $idx < 26) {
            $freq[$idx]++;
        }
    }
    return $freq;
}

// -----------------------------
// FLAMES computation
// Rules (from the PDF):
// - Count similar letters between the two names.
// - Add the total counts, then total % 6.
// - Map remainder to FLAMES: 1=F,2=L,3=A,4=M,5=E,0=S
// -----------------------------
function compute_flames(string $name1Raw, string $name2Raw): array
{
    $n1 = normalize_name($name1Raw);
    $n2 = normalize_name($name2Raw);

    $f1 = letter_frequencies($n1);
    $f2 = letter_frequencies($n2);

    $commonLetters = [];
    $count1 = 0;
    $count2 = 0;

    // For each letter A-Z: if both have it, it's a common letter.
    // We then add ALL occurrences of that letter per name (matches the example).
    for ($i = 0; $i < 26; $i++) {
        if ($f1[$i] > 0 && $f2[$i] > 0) {
            $letter = chr(ord('A') + $i);
            $commonLetters[] = $letter;
            $count1 += $f1[$i];
            $count2 += $f2[$i];
        }
    }

    $total = $count1 + $count2;
    $remainder = $total % 6;

    // remainder -> meaning
    $map = [
        1 => ['F', 'Friends'],
        2 => ['L', 'Lovers'],
        3 => ['A', 'Anger'],
        4 => ['M', 'Married'],
        5 => ['E', 'Engaged'],
        0 => ['S', 'Soulmates'],
    ];

    [$letter, $meaning] = $map[$remainder];

    return [
        'normalized1' => $n1,
        'normalized2' => $n2,
        'commonLetters' => $commonLetters,
        'count1' => $count1,
        'count2' => $count2,
        'total' => $total,
        'remainder' => $remainder,
        'flamesLetter' => $letter,
        'flamesMeaning' => $meaning,
    ];
}

// -----------------------------
// Zodiac Sign computation (Western astrology, common school mapping)
// -----------------------------
function get_zodiac_sign(int $month, int $day): string
{
    // Month-day boundaries for zodiac signs
    // Capricorn: Dec 22 - Jan 19
    // Aquarius:  Jan 20 - Feb 18
    // Pisces:    Feb 19 - Mar 20
    // Aries:     Mar 21 - Apr 19
    // Taurus:    Apr 20 - May 20
    // Gemini:    May 21 - Jun 20
    // Cancer:    Jun 21 - Jul 22
    // Leo:       Jul 23 - Aug 22
    // Virgo:     Aug 23 - Sep 22
    // Libra:     Sep 23 - Oct 22
    // Scorpio:   Oct 23 - Nov 21
    // Sagittarius: Nov 22 - Dec 21

    if (($month == 12 && $day >= 22) || ($month == 1 && $day <= 19)) return "Capricorn";
    if (($month == 1 && $day >= 20) || ($month == 2 && $day <= 18)) return "Aquarius";
    if (($month == 2 && $day >= 19) || ($month == 3 && $day <= 20)) return "Pisces";
    if (($month == 3 && $day >= 21) || ($month == 4 && $day <= 19)) return "Aries";
    if (($month == 4 && $day >= 20) || ($month == 5 && $day <= 20)) return "Taurus";
    if (($month == 5 && $day >= 21) || ($month == 6 && $day <= 20)) return "Gemini";
    if (($month == 6 && $day >= 21) || ($month == 7 && $day <= 22)) return "Cancer";
    if (($month == 7 && $day >= 23) || ($month == 8 && $day <= 22)) return "Leo";
    if (($month == 8 && $day >= 23) || ($month == 9 && $day <= 22)) return "Virgo";
    if (($month == 9 && $day >= 23) || ($month == 10 && $day <= 22)) return "Libra";
    if (($month == 10 && $day >= 23) || ($month == 11 && $day <= 21)) return "Scorpio";
    return "Sagittarius"; // Nov 22 - Dec 21
}

// -----------------------------
// Read user input (POST), validate, and compute
// -----------------------------
$errors = [];
$result = null;

$name1 = $_POST['name1'] ?? '';
$name2 = $_POST['name2'] ?? '';
$bday1 = $_POST['bday1'] ?? '';
$bday2 = $_POST['bday2'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    if (trim($name1) === '') $errors[] = "Please enter Name 1.";
    if (trim($name2) === '') $errors[] = "Please enter Name 2.";
    if (trim($bday1) === '') $errors[] = "Please enter Birthday for Name 1.";
    if (trim($bday2) === '') $errors[] = "Please enter Birthday for Name 2.";

    // Validate date format (YYYY-MM-DD)
    $dt1 = $bday1 ? date_create($bday1) : false;
    $dt2 = $bday2 ? date_create($bday2) : false;

    if ($bday1 && !$dt1) $errors[] = "Birthday for Name 1 is not a valid date.";
    if ($bday2 && !$dt2) $errors[] = "Birthday for Name 2 is not a valid date.";

    if (count($errors) === 0 && $dt1 && $dt2) {
        $zodiac1 = get_zodiac_sign((int)$dt1->format('n'), (int)$dt1->format('j'));
        $zodiac2 = get_zodiac_sign((int)$dt2->format('n'), (int)$dt2->format('j'));

        $flames = compute_flames($name1, $name2);

        $result = [
            'name1' => $name1,
            'name2' => $name2,
            'bday1' => $bday1,
            'bday2' => $bday2,
            'zodiac1' => $zodiac1,
            'zodiac2' => $zodiac2,
            'flames' => $flames,
        ];
    }
}

// If this is an AJAX / fetch POST, respond with JSON instead of full HTML.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $response = [
        'errors' => $errors,
        'result' => $result,
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// If someone visits the PHP endpoint via GET, show a small informational message.
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>LE2 FLAMES Endpoint</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container">
    <section class="card">
      <h1>FLAMES Endpoint</h1>
      <p>This endpoint expects a POST request (fields: <b>name1</b>, <b>bday1</b>, <b>name2</b>, <b>bday2</b>) and returns JSON.</p>
      <p>Open <a href="index.html">index.html</a> to use the form UI.</p>
    </section>
  </main>
</body>
</html>
