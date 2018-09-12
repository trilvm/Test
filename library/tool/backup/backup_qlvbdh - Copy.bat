@For /F "tokens=2,3,4 delims=/ " %%A in ('Date /t') do @( 
	Set MM=%%A
	Set DD=%%B
	Set YYYY=%%C
)
@For /F "tokens=1,2,3,4 delims=:,. " %%A in ('echo %time%') do @(
Set H=%%A
Set M=%%B
Set S=%%C
Set MS=%%D
)
set FOLDERDATA=C:\btyc41
set FOLDER_SUB=%YYYY%\%MM%\%DD%
set FOLDER_BACKUP=d:\backup
set DATABASE_NAME=btyc4

md %FOLDER_BACKUP%\%DATABASE_NAME%\%YYYY%\%MM%\%DD%

c:\7zip\7za.exe a -tzip %FOLDER_BACKUP%\%DATABASE_NAME%\%YYYY%\%MM%\%DD%\%H%%M%_%S%%MS%.zip %FOLDERDATA%