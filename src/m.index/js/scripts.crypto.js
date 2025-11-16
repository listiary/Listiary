async function decrypt(cyphertext, pass) {

	var keyBytes = CryptoJS.PBKDF2(pass, 'worldinlists', { keySize: 48 / 4, iterations: 1000 });

	//take first 32 bytes as key (like in C# code)
	var key = new CryptoJS.lib.WordArray.init(keyBytes.words, 32);
	var seekey = _wordArrayToByteArray(key, 32);
	console.log(seekey);

	//skip first 32 bytes and take next 16 bytes as IV
	var iv = new CryptoJS.lib.WordArray.init(keyBytes.words.splice(32 / 4), 16);
	var seeiv = _wordArrayToByteArray(iv, 16);
	console.log(seeiv);

	var plaintextArray = CryptoJS.AES.decrypt(
	{
		ciphertext: CryptoJS.enc.Base64.parse(cyphertext),
		salt: "worldinlists"
	},
		key,
		{ iv: iv }
	);

	var result = _hex2a(plaintextArray.toString());
	//console.log(result);
	return result;
}
function _wordArrayToByteArray(wordArray, length) {

	if (wordArray.hasOwnProperty("sigBytes") && wordArray.hasOwnProperty("words")) {
		length = wordArray.sigBytes;
		wordArray = wordArray.words;
	}

	var result = [],
		bytes,
		i = 0;
	while (length > 0) {
		bytes = _wordToByteArray(wordArray[i], Math.min(4, length));
		length -= bytes.length;
		result.push(bytes);
		i++;
	}
	return [].concat.apply([], result);
}
function _wordToByteArray(word, length) {
	var ba = [],
		i,
		xFF = 0xFF;
	if (length > 0)
		ba.push(word >>> 24);
	if (length > 1)
		ba.push((word >>> 16) & xFF);
	if (length > 2)
		ba.push((word >>> 8) & xFF);
	if (length > 3)
		ba.push(word & xFF);

	return ba;
}
function _hex2a(hex) {

	// Convert hex string to ASCII.
	// See https://stackoverflow.com/questions/11889329/word-array-to-string
	var str = '';
	var skip = false;
	for (var i = 0; i < hex.length; i += 2)
	{
		if(skip)
		{
			skip = false;
			continue;
		}
		else
		{
			skip = true;
			str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
		}
	}
	return str;
}
function _hexToBytes(hex) {

	//https://stackoverflow.com/questions/14603205/how-to-convert-hex-string-into-a-bytes-array-and-a-bytes-array-in-the-hex-strin
	let bytes = [];
    for (let c = 0; c < hex.length; c += 2)
	{
        bytes.push(parseInt(hex.substr(c, 2), 16));
	}
    return bytes;
}
function _bytesToHex(bytes) {

	let hex = [];
    for (let i = 0; i < bytes.length; i++) {
        let current = bytes[i] < 0 ? bytes[i] + 256 : bytes[i];
        hex.push((current >>> 4).toString(16));
        hex.push((current & 0xF).toString(16));
    }
    return hex.join("");
}
