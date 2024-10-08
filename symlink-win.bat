set /p target="Set the target project: "
clear
mkdir %target%\packages
mkdir %target%\packages\GemaDigital

clear
mklink /D "%target%\packages\GemaDigital" %cd%
pause