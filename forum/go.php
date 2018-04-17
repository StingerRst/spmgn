if( array_key_exists('to', $_GET))
{
	$redirect = $_GET['to'];
	header("HTTP/1.0: 301 Moved Permanentlyn"); //тут еще стоит подумать над статусом
	header("Location: " . $redirect);
}
else
{
	header("HTTP/1.0: 404 Not Foundn");
	print "Извините, ссылка не найдена";
}