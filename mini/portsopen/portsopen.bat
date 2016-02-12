@echo off
setlocal
:: DOS Batch script.
:: See help under Subroutine section below for full details.
::
:: Primary purpose: Given host and list of ports,
:: Checks to see if port is open.
:: 
:: If port is closed, try to start related service.

:: Begin Main Body
:: =========================================================

:: All parameters for logging purposes
set portsopen_all_parameters=%0 %*

:: Identify log file and temp files
set portsopen_log_file=%~n0.log
set portsopen_temp_output=%~n0_output.tmp
set portsopen_temp_nmap=%~n0_nmap.tmp


:: Send user to help message depending on input
call :check_if_asking_for_help %1
if %errorlevel% equ 1 (
  call :give_help_message %0
  exit /b 0
)


:: Optional first parameter is "open" (default) or "close"
:: Open is 0 and Close is 1
set portsopen_mode=0
if "%1"=="close" (
  set portsopen_mode=1
  shift
)
if "%1"=="open" shift


:: Send user to help if not enough parameters
if "%2"=="" (
  call :give_help_message %0
  exit /b 0
)


:: Catch problems and set errorlevel at end of batch
set portsopen_final_errorlevel=0


:: Our host or IP to Check
set portsopen_host=%1


:: Initiate Logging.
call :begin_logging %portsopen_all_parameters%


:: Loop through space delimited list of ports to check.
:: Note: Using gotos instead of if <expr> (multi commands)
::       To avoid DOS Batch feature of not updating %expansions% inside parantheses
:checkport_begin
  if "%2"=="" goto checkport_end

  :: Check status of port
  call :check_port_status %portsopen_host% %2
  set portsopen_status=%errorlevel%

  :: If already in the desired state, output that result
  if %portsopen_status% equ %portsopen_mode% (
    call :output_status %portsopen_host% %2 %portsopen_status% "is already"
    goto check_next_port
  )

  :: If not already in the desired state, try to change it, then check status again
  call :change_port_status %portsopen_host% %2 %portsopen_mode%

  :: If change_port_status was unable to try, skip re-testing the port
  if %errorlevel% equ 1 goto skip_retest

  :: Retest port status
  call :check_port_status %portsopen_host% %2
  set portsopen_status=%errorlevel%
  :skip_retest

  :: Output changed status
  if %portsopen_status% equ %portsopen_mode% (
    call :output_status %portsopen_host% %2 %portsopen_status% "is now"
  )

  :: Output state not changed
  if %portsopen_status% neq %portsopen_mode% (
    call :output_status %portsopen_host% %2 %portsopen_status% "is still" "(include_error_message)"
    set portsopen_final_errorlevel=1
  )

  :check_next_port
  shift
  goto checkport_begin
:checkport_end

:: Clean up tmp file and we are done
del %portsopen_temp_output% 2> nul
del %portsopen_temp_nmap% 2> nul
exit /b %portsopen_final_errorlevel%
:: =========================================================
:: End Main Body



:: Begin Subroutine section
:: =========================================================



:check_if_asking_for_help
setlocal
:: ---------------------------------------------------------
:: Is user asking for help?
set portsopen_help=no
if "%1"=="" set portsopen_help=yes
if "%1"=="?" set portsopen_help=yes
if "%1"=="/?" set portsopen_help=yes
if /I "%1"=="/h" set portsopen_help=yes
if /I "%1"=="/help" set portsopen_help=yes
if /I "%1"=="help" set portsopen_help=yes
if /I "%1"=="-h" set portsopen_help=yes
if /I "%1"=="-help" set portsopen_help=yes
if /I "%1"=="--help" set portsopen_help=yes
if %portsopen_help%==yes exit /b 1
if %portsopen_help%==no exit /b 0



:give_help_message
setlocal
:: ---------------------------------------------------------
echo.
echo -------------------------------------------------------
echo Notes on %1: 
echo    1. This DOS batch script requires installation of Nmap
echo       See: https://nmap.org
echo.
echo    2. Edit this %1 script to configure commands that 
echo       start and stop servers on your host to open or close ports.
echo.
echo    3. Output is sent to the screen and also to %~n0.log
echo.
echo Usage: %1 [open(default)^|close] {host or IP} {port [port] [port...]}
echo.
echo Examples:
echo.
echo rem  Make sure web server is up at 192.168.33.10 (port 80 is open)
echo %1 192.168.33.10 80
echo.
echo rem  Make sure Web server and Remote Procedure Calls are up
echo rem  (ports 80 and 111 are open)
echo %1 192.168.33.10 80 111
echo.
echo rem  Stop Remote Procedure service (port 111 is closed)
echo %1 close 192.168.33.10 111
echo -------------------------------------------------------
echo.
exit /b 0



:begin_logging
setlocal
:: ---------------------------------------------------------
echo ========================================================= > %portsopen_temp_output%
echo %DATE%  %TIME% >> %portsopen_temp_output%
echo Command: %* >> %portsopen_temp_output%
echo. >> %portsopen_temp_output%
call :log_output
exit /b 0



:echo_output
setlocal
:: ---------------------------------------------------------
type %portsopen_temp_output%
exit /b 0



:log_output
setlocal
:: ---------------------------------------------------------
type %portsopen_temp_output% >> %portsopen_log_file%
exit /b 0



:check_port_status
setlocal
:: ---------------------------------------------------------
:: Parameters
::  1 is the host or IP
::  2 is the port number
:: Return 0 if open, 1 otherwise

:: Show the command for the benefit of the user at the terminal
echo Testing port status: nmap -p %2 %1

:: Issue the command and capture the result
nmap -p %2 %1 > %portsopen_temp_nmap%

:: Filter first on lines with " open ", next look for port #/
type %portsopen_temp_nmap% | find " open " | find "%2/" > nul
exit /b %errorlevel%



:output_status
setlocal
:: ---------------------------------------------------------
:: Parameters:
::  1 is the host or IP
::  2 is the port number
::  3 is the state 0 open, 1 closed
::  4 is a message like "is already", "is now", "is still"
::  5 is not empty to append to the contents of the portsopen_temp_output
::

:: If paramter 5 is empty erase any collected output
if "%5"=="" del %portsopen_temp_output% 2> nul

:: If paramter 5 is not empty add a spacing line at the end
if not "%5"=="" echo. >> %portsopen_temp_output%

:: Our status
set our_status=open
if %3 equ 1 set our_status=closed

:: Strip the quotes from our message
set our_message_with_quotes=%4
set our_message_without_quotes=%our_message_with_quotes:"=%
:: " 
:: The above comment fixes the display in my editor 
:: because the line above that has just one double quote 
:: which makes my editor think the string continues for the
:: rest of the script!

:: Put the whole message together
echo Port %2 on %1 %our_message_without_quotes% %our_status% >> %portsopen_temp_output%
echo. >> %portsopen_temp_output%

:: Send it to both terminal and log
call :echo_output
call :log_output

exit /b 0



:execute_command
setlocal
:: ---------------------------------------------------------
:: Show command for the benefit of the user at the terminal
echo Changing port status: %portsopen_command%

:: We are collecting the command and output for possible
:: display and logging later.
echo The following failed: > %portsopen_temp_output%
echo %portsopen_command% >> %portsopen_temp_output%

:: The 2>&1 is intended to capture any error messages to 
:: the same file as the standard output
:: See https://en.wikibooks.org/wiki/Windows_Batch_Scripting#Redirection
%portsopen_command% >> %portsopen_temp_output% 2>&1

:: We don't know at this point if there was success or failure
exit /b 0



:vagrant_1
:: Don't use setlocal because
:: this needs to update the caller's portsopen_command variable
:: ====================================================
:: = This is a host type command generator subroutine =
:: ====================================================
:: Parameters
::  1 is the host or IP
::  2 is the port number
::  3 is desired state: 0 open, 1 closed
:: Return 0 if we were able to populate the portsopen_command variable
::   otherwise return 1
:: 
:: This routine may be extended or duplicated and modified
:: to handle additional hosts and ports 

:: Match service name to port or simply return failure
set portsopen_service=
if "%2"=="80" set portsopen_service=apache2
if "%2"=="111" set portsopen_service=rpcbind
if "%portsopen_service%"=="" exit /b 1

:: Tell service to start or stop
set portsopen_service_action=start
if %3 equ 1 set portsopen_service_action=stop

:: Build command to execute and return success
set portsopen_command=vagrant ssh -c "sudo service %portsopen_service% %portsopen_service_action%"
exit /b 0



:change_port_status
setlocal
:: ---------------------------------------------------------
:: Parameters
::  1 is the host or IP
::  2 is the port number
::  3 is desired state 0 open, 1 closed
:: Return 0 if port change attempted, 1 otherwise
::
:: Note:
:: This section of code is expected to be extended for your application
:: Here we demonstrate two changeable ports on a Ubuntu Vagrant box

:: The name of the subroutine that builds the
:: the command changing the port on the given given host
set portsopen_host_command_generator=

:: Are we scripted to handle this host?
:: ==============================================================================
:: = This is where we may make edits to handle new hosts and host types         =
:: = We may also need to create or edit host type command generator subroutines =
:: ==============================================================================
if "%1"=="192.168.33.10" call set portsopen_host_command_generator=vagrant_1

:: Variables updated or used by the command generator
set portsopen_command=(unknown command)
set portsopen_service=(unknown service)
set portsopen_service_action=(unknown action)

:: Assume no command to start or stop service is determined
echo No command scripted for host %1 port %2 > %portsopen_temp_output%

:: Return if no host type command generator subroutine identified
if "%portsopen_host_command_generator%"=="" exit /b 1

:: Call host type command generator subroutine 
call :%portsopen_host_command_generator% %1 %2 %3

:: Return if no command available
if %errorlevel% neq 0 exit /b 1

:: Execute the command
call :execute_command

:: Return
exit /b 0

:: End Subroutine section and also end the script
:: =========================================================
