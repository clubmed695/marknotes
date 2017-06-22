@ECHO OFF

REM --- Please adapt this location to reflect your installation; the folder where you've put marknotes source files
SET MASTER=C:\Christophe\Repository\markdown\src\

CALL :ShowInfo
:DoIt

GOTO :END


REM ---------------------------------------------------------------------------------------------------------
:ShowInfo
CLS

ECHO  ===========================================================================
ECHO  =                                                                         =
ECHO  = Marknotes                                                               =
ECHO  =                                                                         =
ECHO  = Quickly deploy a copy of marknotes, on a localhost, by just referencing =
ECHO  = source files so a change in a source file will be reflected in the copy =
ECHO  =                                                                         =
ECHO  = The added-value of this script is to avoid, when you need to have more  =
ECHO  = than one "marknotes" website on your system, to duplicate every files.  =
ECHO  =                                                                         =
ECHO  = Under Windows OS, the mklink command allow to create a symlink (like a  =
ECHO  = shortcut) and files should only be there once on your system.           =
ECHO  =                                                                         =
ECHO  ===========================================================================

ECHO.
ECHO  This script can be used to quickly create a new local website for marknotes.
ECHO  A copy of marknotes should be present in the %MASTER% 
ECHO  folder (or edit and change this script for an another location) and symbolic
ECHO  links will be created to that folder for marknotes files and folders.
ECHO.
ECHO  Only the \docs folder and the \settings.json file will be specific to the new local website.
ECHO.
ECHO  Press CTRL+C to stop this script or press the enter key to continue.
ECHO.
ECHO  (this script should be started in a Windows command prompt with admin privileges)
ECHO.
PAUSE

REM ---------------------------------------------------------------------------------------------------------
:DoIt
mklink /D assets %MASTER%assets
mklink /D libs %MASTER%libs
mklink /D marknotes %MASTER%marknotes
mklink /D templates %MASTER%templates

if not exist "docs" mkdir docs

mklink .editorconfig %MASTER%.editorconfig
mklink .htaccess %MASTER%.htaccess
mklink .htmlhintrc %MASTER%.htmlhintrc
mklink .ignore %MASTER%.ignore
mklink .jsbeautifyrc %MASTER%.jsbeautifyrc
mklink autoload.php %MASTER%autoload.php
mklink browserconfig.xml %MASTER%browserconfig.xml
mklink cli.php %MASTER%cli.php
mklink custom.css.dist %MASTER%custom.css.dist
mklink custom.js.dist %MASTER%custom.js.dist
mklink error_php.html %MASTER%error_php.html
mklink favicon.ico %MASTER%favicon.ico
mklink index.php %MASTER%index.php
mklink package.json %MASTER%package.json
mklink readme.md %MASTER%readme.md
mklink router.php %MASTER%router.php
mklink ruleset.xml %MASTER%ruleset.xml
mklink settings.json.dist %MASTER%settings.json.dist

if not exist "tags.json" echo {} > tags.json

if not exist "settings.json" echo {"debug":1,"plugins":{"options":{"login":{"username":"","password":""},"optimisation":{"browser_cache":0,"localStorage":0,"server_session":0}}},"site_name":"Test site"} > settings.json

REM ---------------------------------------------------------------------------------------------------------
:END