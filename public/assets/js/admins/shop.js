function copyBankDetails() {
	const text = document.getElementById('bank-details').innerText;
	navigator.clipboard.writeText(text).then(() => {
    }).catch(err => {
        console.error("Failed to copy: ", err);
    });
}