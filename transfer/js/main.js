function startTransfer() {
    $('res').innerHTML = '<img src="process.gif" />';
    var AU = new Ajax.Updater('res', 'trans.php?'+sid,
        {
            method: 'post',
            parameters: $('DB').serialize()+$('info').serialize()
        }
    );
}