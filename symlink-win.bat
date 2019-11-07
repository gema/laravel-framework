set /p target="Set the target project: "
clear
mkdir %target%\packages
mkdir %target%\packages\gemadigital

clear
mklink /D "%target%\packages\gemadigital\framework" %cd%
pause