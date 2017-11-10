<?php
/**
 * Rename a file.
 *
 * Anwser to URL like the one below (names are base64_encoded)
 * index.php?task=task.file.rename&oldname=emEyJTJGYQ%3D%3D&newname=emEyJTJGYWVyYXpl
 */
namespace MarkNotes\Plugins\Task\File;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(__FILE__).DS.'.plugin.php');

class Rename extends \MarkNotes\Plugins\Task\File
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.file';
	protected static $json_options = '';

	/**
	 * Rename an existing note
	 */
	private static function rename(string $oldname, string $newname) : float
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if (trim($oldname) === '') {
			return FILE_ERROR;
		}

		// Sanitize filenames
		$oldname = $aeFiles->sanitizeFileName($oldname);
		$oldname = $aeSettings->getFolderDocs().$oldname;

		$newname = $aeFiles->sanitizeFileName($newname);
		$newname = $aeSettings->getFolderDocs().$newname;

		// Try to remove the folder, first, be sure that the user
		// can see the folder : if he can't, he can't delete it too
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('task.acls.cansee');

		// Note : the folder should start and end with the slash
		$arr = array('folder' => dirname($oldname),'return' => true);
		$args = array(&$arr);

		$aeEvents->trigger('task.acls.cansee::run', $args);

		// cansee will initialize return to 0 if the user can't
		// see the folder
		if (intval($args[0]['return'])===1) {
			// Only if the user can see the parent folder, he can rename files

			// Before renaming the file (f.i. note.md), check if we've another
			// files with the same name but with other extensions (like note.json,
			// note.html, ...) and rename these files too.
			$tmp = $aeFiles->removeExtension($oldname);
			$arrFiles = glob($tmp.'.*');

			$wReturn = true;
			$new = $aeFiles->removeExtension($newname);

			foreach ($arrFiles as $file) {
				$wReturn = $aeFiles->renameFile($file, $new.'.'.pathinfo($file)['extension']);
			}

			return ($wReturn ? RENAME_SUCCESS : FILE_ERROR);
		} else {
			return NO_ACCESS;
		}
	}

	/**
	 * Rename a note on the disk
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Be sure that filenames doesn't already start with the /docs folder
		self::cleanUp($params, $aeSettings->getFolderDocs(false));

		$newname = trim(urldecode($aeFunctions->getParam('param', 'string', '', true)));
		if ($newname != '') {
			$newname = $aeFiles->sanitizeFileName(trim($newname));
		}

		$oldname = trim(urldecode($aeFunctions->getParam('oldname', 'string', '', true)));

		if ($oldname != '') {
			$oldname = $aeFiles->sanitizeFileName(trim($oldname));
		}

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log(__METHOD__, 'debug');
			$aeDebug->log('Oldname=['.$oldname.']', 'debug');
			$aeDebug->log('Newname=['.$newname.']', 'debug');
		}
		/*<!-- endbuild -->*/

		if (trim($newname) === '') {
			$return = array(
				'status' => 0,
				'action' => 'rename',
				'msg' => $aeSettings->getText('unknown_error', 'An error has occured, please try again')
			);
		} else {
			$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

			// Be sure to have the .md extension
			$oldname = $aeFiles->removeExtension($oldname).'.md';
			$newname = $aeFiles->removeExtension($newname).'.md';

			// Relative filenames
			$rel_oldname = str_replace($aeSettings->getFolderDocs(true), '', $oldname);
			$rel_newname = str_replace($aeSettings->getFolderDocs(true), '', $newname);

			// Try to create a file called "$filename.md" on the disk
			$wReturn = self::rename($oldname, $newname);

			switch ($wReturn) {
				case RENAME_SUCCESS:
					$msg = $aeSettings->getText('file_renamed', 'The note [%1] has been renamed into [%2]');
					$msg = str_replace('$1', $rel_oldname, $msg);
					$msg = str_replace('$2', $rel_newname, $msg);
					break;
				case NO_ACCESS:
					// The parent folder is protected and the user has no access to it
					$msg = $aeSettings->getText('folder_parent_not_accessible', 'The parent folder of [$1] is not accessible to you');
					$msg = str_replace('$1', $rel_oldname, $msg);
					break;
				default:
					$msg = $aeSettings->getText('error_rename_file', 'An error has occured when trying to rename the note [%1] into [%2]');
					$msg = str_replace('$1', $rel_oldname, $msg);
					$msg = str_replace('$2', $rel_newname, $msg);
					break;
			} // switch ($wReturn)

			$return = array(
				'status' => (($wReturn == RENAME_SUCCESS) ? 1 : 0),
				'action' => 'rename',
				'msg' => $msg,
				'md5' => md5($docs.$newname),
				'filename' => utf8_encode($newname)
			);
		}

		header('Content-Type: application/json');
		echo self::returnInfo($return);

		return true;
	}
}
