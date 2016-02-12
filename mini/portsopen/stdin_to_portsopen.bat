@echo off
:: Send Standard Input into a portsopen batch
:: Credits: http://stackoverflow.com/questions/6979747/read-stdin-stream-in-a-batch-file
setlocal DisableDelayedExpansion

for /F "tokens=*" %%a in ('findstr /n $') do (
  set "line=%%a"
  setlocal EnableDelayedExpansion
  set "line=!line:*:=!"
  call portsopen.bat !line!
  endlocal
)