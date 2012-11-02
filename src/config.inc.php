<?php
/* Настройки скрипта */

$isRoman = true;			// Номера книг могут быть римскими цифрами
$doCorrection = true;		// Исправлять названия книг на стандартные
$languageIn = 'ru';			// Язык анализируемых (пока не работает)
$languageOut = 'ua';		// Язык вывода (ru, ua)

// Разделители между главами и стихами (рус. Мф. 3:4–6,8 и укр. Мт. 3,4–6.8)
if ($languageIn == 'ua') {
	$СhapterSeparatorVerseIn = ',';
	$VerseSeparatorVerseIn = '.';
} else {
	$СhapterSeparatorVerseIn = ':';
	$VerseSeparatorVerseIn = ',';
}
if ($languageOut == 'ua') {
	$СhapterSeparatorVerseOut = ',';
	$VerseSeparatorVerseOut = '.';
} else {
	$СhapterSeparatorVerseOut = ':';
	$VerseSeparatorVerseOut = ',';
}

// Выбор источника онлайн Библии:
// 0 — http://allbible.info/ 		(рус., укр. или англ.)
// 1 — http://bible.com.ua/ 		(рус., укр. и англ. одновременно)
// 2 — http://biblezoom.ru/ 		(греч. с подстрочником)
// 3 — http://bibleonline.ru/ 		(рус., укр., бел. или англ.)
// 4 — http://bible-center.ru/ 		(рус., англ., греч. и лат.)
// 5 — http://bibleserver.com/ 		(рус., болг., англ., греч., ивр. и лат.)
$source = 5;

// Выбор перевода для allbible.info:
$translationAllBible = 'sinodal/'; 			// Русский синодальный перевод (рус.)
//$translationAllBible = 'modern/'; 		// Современный (рус.)
//$translationAllBible = 'modernrbo/'; 		// Перевод РБО. Радостная Весть (рус., только НЗ)
//$translationAllBible = 'ogienko/';		// Український переклад Огієнка (укр.)
//$translationAllBible = 'kingjames/';		// King James Version (англ.)
//$translationAllBible = 'standart/';		// American Standard Version (англ.)

// Выбор перевода для bibleonline.ru:
$translationBibleOnline = 'rus/';			// Русский синодальный перевод (рус.)
//$translationBibleOnline = 'cas/'; 		// Перевод еп. Кассиана (рус., только НЗ)
//$translationBibleOnline = 'rbo/'; 		// Перевод РБО. Радостная Весть (рус., только НЗ)
//$translationBibleOnline = 'csl/'; 		// Церковнославянский перевод
//$translationBibleOnline = 'ukr/'; 		// Український переклад Огієнка (укр.)
//$translationBibleOnline = 'eng/'; 		// King James Version (англ.)
//$translationBibleOnline = 'bel/'; 		// Беларускі пераклад (бел.)

// Выбор перевода для bible-center.ru:
$translationBibleCenter = 'synnew_ru/';		// Русский синодальный перевод (рус.)
//$translationBibleCenter = 'kassian_ru/'; 	// Перевод еп. Кассиана (рус., только НЗ)
//$translationBibleCenter = 'rv_ru/'; 		// Перевод РБО. Радостная Весть (рус., только НЗ)
//$translationBibleCenter = 'kulakov_ru/'; 	// Перевод Кулакова (рус., только НЗ)
//$translationBibleCenter = 'slavonic_ru/'; // Церковнославянский перевод
//$translationBibleCenter = 'kjv_eng/'; 	// King James Version (англ.)
//$translationBibleCenter = 'nasb_eng/'; 	// New American Standard Bible (англ.)
//$translationBibleCenter = 'niv_eng/'; 	// New International Version (англ.)
//$translationBibleCenter = 'nv_lat/'; 		// Новая Вульгата (лат.)
//$translationBibleCenter = 'sept_gr/'; 	// Септуагинта (греч.)

// Выбор перевода для bibleserver.com:
$translationBibleServer = 'RUS/';			// Новый перевод на русский язык (рус.)
//$translationBibleServer = 'CRS/';			// Священное Писание (рус.)
//$translationBibleServer = 'BLG/';			// Българската Библия (болг.)
//$translationBibleServer = 'ESV/';			// English Standard Version (англ.)
//$translationBibleServer = 'NIV/';			// New International Version (англ.)
//$translationBibleServer = 'TNIV/';		// Today's New International Version (англ.)
//$translationBibleServer = 'NIRV/';		// New International Readers Version (англ.)
//$translationBibleServer = 'KJV/';			// King James Version (англ.)
//$translationBibleServer = 'KJVS/';		// King James Version with Strong's Dictionary (англ.)
//$translationBibleServer = 'LXX/';			// Septuaginta (греч.)
//$translationBibleServer = 'OT/';			// Hebrew OT (ивр.)
//$translationBibleServer = 'VUL/';			// Vulgata (лат.)

// Примечание! В разных сайтах нумерация стихов может отличаться, особенно для Псалмов.
?>