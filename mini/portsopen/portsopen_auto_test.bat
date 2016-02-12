@echo off
setlocal
:: Automated testing for portsopen.bat and stdin_to_portsopen.bat
:: Strategy:
::   Use Ports 80 (apache2 web server), 
::             99 assumed closed with no support to start server
::             111 (rpcbind)
::
::   1. Perform a self test that generates both a Passed and Failed line
::
::   2. Verify Nmap is installed (See https://nmap.org)
::
::   3. Start by attempting to close all ports with direct call to portsopen.bat
::      Verify all above ports closed according to nmap utility
::
::   4. Attempt to open each port with standard input to stdin_to_portsopen.bat
::      .1 Verify 80 and 111 are open and 99 is closed according to nmap
::      .2 Verify portsopen.log has 80 and 111 are "now open" 99 is "still closed"
::
::   5. Attempt to open each port with direct call to portsopen.bat
::      Verify portsopen.log 80 and 11 are "already open"
::  

:: Host 
set test_host=192.168.33.10

:: Log file
set test_log=portsopen_auto_test.log

:: Temp file  
set test_output=test_output.tmp

:: Start test log
echo ========================================================== > %test_log%
echo %DATE% %TIME% portsopen_auto_test >> %test_log%
echo. >> %test_log%


echo.
echo Performing Test 1 (Self test)...
           echo Test 1 (Self test should generate one Passed and one Failed): >> %test_log%
call :self_test
if %errorlevel% gtr 0 exit /b 1
echo. >> %test_log%



echo.
echo Performing Test 2 (Nmap installed)...
           echo Test 2 (Nmap installed) >> %test_log%

echo Command: nmap -h ^> %test_output% >> %test_log%
              nmap -h  > %test_output%

echo ---------------------------------------------------------- >> %test_log%
call :verify "Test 2" %test_output% "nmap" ".org"
echo. >> %test_log%



echo.
echo Performing Test 3 (Close ports with portsopen.bat)...
           echo Test 3 (Close ports with portsopen.bat) >> %test_log%

echo Command: call portsopen.bat close %test_host% 80 99 111 >> %test_log%
              call portsopen.bat close %test_host% 80 99 111

echo Command: nmap -p 80,99,111 %test_host% ^> %test_output% >> %test_log%
              nmap -p 80,99,111 %test_host%  > %test_output%

echo ---------------------------------------------------------- >> %test_log%
call :verify "Test 3a" %test_output% " closed " "80"
call :verify "Test 3b" %test_output% " closed " "99"
call :verify "Test 3c" %test_output% " closed " "111"
echo. >> %test_log%



echo.
echo Performing Test 4 (Open ports via standard input)...
           echo Test 4 (Open ports via standard input) >> %test_log%
           echo      4.1 Verify 80 and 111 are open and 99 is closed according to nmap >> %test_log%
           echo      4.2 Verify 80 and 111 are "now open" and 99 is "still closed" according to portsopen.log >> %test_log%

echo Command: call :erasefile portsopen.log >> %test_log%
              call :erasefile portsopen.log

echo Command: call :standard_input "%test_host% 80" "%test_host% 99 111" >> %test_log%
              call :standard_input "%test_host% 80" "%test_host% 99 111"

echo Command: nmap -p 80,99,111 %test_host% ^> %test_output% >> %test_log%
              nmap -p 80,99,111 %test_host%  > %test_output%

echo ---------------------------------------------------------- >> %test_log%
call :verify "Test 4.1.a" %test_output% " open " "80"
call :verify "Test 4.1.b" %test_output% " closed " "99"
call :verify "Test 4.1.c" %test_output% " open " "111"
call :verify "Test 4.2.a  " portsopen.log "now open" "80"
call :verify "Test 4.2.b  " portsopen.log "still closed" "99"
call :verify "Test 4.2.c  " portsopen.log "now open" "111"
echo. >> %test_log%



echo.
echo Performing Test 5 (Verify ports are already open)...
           echo Test 5 (Verify ports are already open) >> %test_log%

echo Command: call portsopen.bat open %test_host% 80 99 111 >> %test_log%
              call portsopen.bat open %test_host% 80 99 111

echo Command: nmap -p 80,99,111 %test_host% ^> %test_output% >> %test_log%
              nmap -p 80,99,111 %test_host%  > %test_output%

echo ---------------------------------------------------------- >> %test_log%
call :verify "Test 5.a" portsopen.log "already open" "80"
call :verify "Test 5.b" portsopen.log "still closed" "99"
call :verify "Test 5.c" portsopen.log "already open" "111"
echo. >> %test_log%


:: End log file
echo End Automated Test >> %test_log%
echo ========================================================== >> %test_log%

:: Output result
type %test_log%

:: Clean up temp file and exit
call :erasefile %test_output%

exit /b 0

:: ======================================================================================
:: Subroutine Section

:verify
setlocal
:: --------------------------------------------------------------------------------------
:: Parameters:
::   1. title
::   2. file
::   3. Find first
::   4. Find second
:: Searches file for first then second arguments.
:: If found writes Verified message into log, otherwise writes Failed message

: Lose the surrounding quotes
set title_q=%1
set title=%title_q:"=%
:: "
set result=Failed
type %2 | find %3 | find %4 > nul
if %errorlevel% equ 0 set result=Passed
echo %title% %2 includes lines with %3 and %4: %result% >> %test_log%
exit /b 0


:standard_input
setlocal
:: --------------------------------------------------------------------------------------
:: Parameters:
::   1. line to send to stdin_to_portsopen.bat
::   2. Next line to send to stdin_to_portsopen.bat
::   3. etc.

:: Start with empty output file
call :erasefile %test_output%

:: Loop to process input of lines of commands
:standard_input_begin
if [%1]==[] goto standard_input_end

:: Lose the surrounding quotes
set line_q=%1
set line=%line_q:"=%
:: " - Just to make editor display code properly by closing quotes

:: Add line to output
echo %line% >> %test_output%

:: Discard parameter and get next one
shift
goto standard_input_begin
:standard_input_end

:: Send lines of commands to standard input
type %test_output% | stdin_to_portsopen.bat
exit /b 0



:erasefile
setlocal
:: --------------------------------------------------------------------------------------
:: Parameters:
::   1. file
:: Erases the file
erase %1 2> nul
exit /b 0


:self_test
setlocal
:: --------------------------------------------------------------------------------------
:: Parameters:
::   None
:: Verifies the verify Subroutine or quits this batch

:: DOS help does include "ATTRIB"
:: but does not include "XXXYYYZZZ"
echo Command: help ^> %test_output% >> %test_log%
              help  > %test_output%

:: Our log should have Passed in it
echo ---------------------------------------------------------- >> %test_log%
call :verify "DOS Help (Passed expected)" %test_output% "ATTRIB" "attributes"
type %test_log% | find "Passed" > nul
if %errorlevel% gtr 0 (
  echo :self_test failed test 1
  exit /b 1
)

:: Our log should have Failed in it
call :verify "DOS Help (Failed expected)" %test_output% "Nothing" "no how!"
type %test_log% | find "Failed" > nul
if %errorlevel% gtr 0 (
  echo :self_test failed test 2
  exit /b 1
)

exit /b 0

:: End Subroutine Section and file
:: ======================================================================================
