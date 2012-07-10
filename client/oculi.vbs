'#######################################################
' oculi.vbs
'#######################################################

' Script Version
strVersion = "0.3.0"

' 0 = Off 
' 1 = On
localDebug = 0

' Domain Name
domainName = "CAMPUS"

' File Destination (Local)
shareName = "\\sharename"

' File Destination (Remote)
rProg = """C:\Program Files\oculi\pscp.exe"""
rIP = "10.10.10.10"
rPort = "444"
rUser = "remote"
rPass = "remote"
rDir = "/PATCHES/"

' You will get gibberish if this isn't set for file open.
Const Unicode = -1

' Create file object
Const ForReading = 1, ForWriting = 2, ForAppending = 8 
Set objFSO = CreateObject("Scripting.FileSystemObject")
Set wshNetwork = CreateObject("wscript.network")
Set Shell = CreateObject("wscript.Shell")

' Current locale
curLocale = GetLocale()
wmicFormat = "/format:CSV"

If curLocale <> 1033 And objFSO.FileExists(Shell.ExpandEnvironmentStrings("%WINDIR%") + "\System32\wbem\en-US\csv.xsl") Then 
    wmicFormat = "/format:""%WINDIR%\System32\wbem\en-US\csv.xsl"""
End If

' Date/Time format
intLocale = SetLocale(1033)

' Timestamp
tTime = Replace(formatDateTime(Now(), 4),":","-")
tYear  = year(date)
tMonth = month(date)
tDay   = day(date)

if tMonth < 10 Then tMonth = 0 & tMonth
if tDay < 10 Then tDay = 0 & tDay

todaysDate = tYear & "-" & tMonth & "-" & tDay

' Hostname
computerName = wshNetWork.ComputerName

' Output
fileShort = todaysDate & "_" & tTime & "_" & computerName & "_KB.txt"
fileName = "C:\Windows\Temp\" & fileShort

' File transport methods. If SMB fails, tRemote is the fallback.
Function tRemote()
    strRun = "%comspec% /c " & rProg & " -batch -P " & rPort & " -l " & rUser & " -pw " & rPass & " " & fileName & " " & rIP & ":" & rDir & fileShort
    runErr = objShell.Run((strRun), 0, True)
    If runErr <> 0 Then
	    objFSO.DeleteFile(fileName), True
        wscript.Quit(1)	
	End If
    objFSO.DeleteFile(fileName), True	
End Function

Function tSMB ()
    On Error Resume Next
    objFSO.MoveFile fileName, shareName
    If objFSO.FileExists(fileName) Then
        tRemote()
    End If
End Function

Function tLocal ()
    wscript.echo "Output can be found here: " & fileName
End Function

' Convert binary value to a (human) readable timestamp
Function formatStamp(tS)
    Term = tS(7)*(2^56) + tS(6)*(2^48) + tS(5)*(2^40) + tS(4)*(2^32) + tS(3)*(2^24) + tS(2)*(2^16) + tS(1)*(2^8) + tS(0)
    Days = Term/(1E7*86400)
    theDate = FormatDateTime(CDate(DateSerial(1601, 1, 1) + Days),2)
    theTime = FormatDateTime(CDate(DateSerial(1601, 1, 1) + Days),4)
	formatStamp = theDate & " " & theTime
End Function

' It is important that the first redirect is a single ">" so that it overwrites
' any existing failed attempt.

' Get KBs
Set objShell = WScript.CreateObject("WScript.Shell")
strRun = "%comspec% /c wmic qfe list brief " & wmicFormat & " > " & fileName
objShell.Run strRun, 0, True

' IP
strRun = "%comspec% /c wmic nicconfig get IPAddress,MACAddress,IPEnabled " & wmicFormat & " >> " & fileName
objShell.Run strRun, 0, True

' OS
strRun = "%comspec% /c wmic OS LIST status " & wmicFormat & " >> "& fileName
objShell.Run strRun, 0, True

' Manufacturer and Model
strComputer = "."
set objWMIService = GetObject("winmgmts:" & "{impersonationLevel=impersonate}!\\" & strComputer & "\root\cimv2")
set colItems = objWMIService.ExecQuery("select Manufacturer, Model from Win32_ComputerSystem")
For Each objItem in colItems
     strManufacturer = objItem.Manufacturer
     strModel = objItem.Model
Next

' Serial number and Service tag
set colItems = objWMIService.ExecQuery("select SerialNumber, SMBIOSAssetTag from Win32_SystemEnclosure")
For Each objItem in colItems
     strSerial = objItem.SerialNumber
     strAsset = objItem.SMBIOSAssetTag
Next

' RAM
Set colItems = objWMIService.ExecQuery("select capacity from Win32_PhysicalMemory")
IntRam = 0
For Each objItem in colItems
     IntRam = IntRam + objItem.capacity
Next

IntRam = Round(IntRam / (1024^3))

' CPU
set colItems = objWMIService.ExecQuery("select name from Win32_Processor")
For Each objItem in colItems
	StrCPU = objItem.name
Next

' Hard Disk. We are just looking for C:
set colItems = objWMIService.ExecQuery("select size from Win32_Logicaldisk where Caption='C:'")
IntDisk = 0
For Each objItem in colItems
    If IsNull (objItem.size) Then
        IntDisk = 0
	Else
	    IntDisk = Round(objItem.size / (1024^3))
    End If		 
Next

' AV checks
Set objFile = objFSO.OpenTextFile(fileName, ForAppending, True, Unicode) 

const HKEY_LOCAL_MACHINE = &H80000002
strComputer = "."
Set oReg=GetObject( _
   "winmgmts:{impersonationLevel=impersonate}!\\" &_
    strComputer & "\root\default:StdRegProv")
strKeyPath = "SOFTWARE\Microsoft\Microsoft Forefront\Client Security\1.0\AM\Signature Updates"

' Engine Version
strValueEngVer = "EngineVersion"
oReg.GetStringValue _
HKEY_LOCAL_MACHINE,strKeyPath,strValueEngVer,EngineVersion

' Antispyware Signature Version
strValueSigVer = "ASSignatureVersion" 
oReg.GetStringValue _
HKEY_LOCAL_MACHINE,strKeyPath,strValueSigVer,ASSigVersion

' Antispyware Signature Applied
strValueName = "ASSignatureApplied"
oReg.GetBinaryValue HKEY_LOCAL_MACHINE,strKeyPath,strValueName,tS
ASSigTime = formatStamp(tS)

' Antivirus Signature Version
strValueSigVer = "AVSignatureVersion" 
oReg.GetStringValue _
HKEY_LOCAL_MACHINE,strKeyPath,strValueSigVer,AVSigVersion

' Antivirus Signature Applied
strValueName = "AVSignatureApplied"
oReg.GetBinaryValue HKEY_LOCAL_MACHINE,strKeyPath,strValueName,tS
AVSigTime = formatStamp(tS)

' Last scan is in a different spot
strKeyPath = "SOFTWARE\Microsoft\Microsoft Forefront\Client Security\1.0\AM\Scan"

'Antivirus Last Scan
strValueName = "LastScanRun"
oReg.GetBinaryValue HKEY_LOCAL_MACHINE,strKeyPath,strValueName,tS
LastRunTime = formatStamp(tS)

' Last Logged on User
strValue = "LastLoggedOnUser"
strKeyPath = "SOFTWARE\Microsoft\Windows\CurrentVersion\Authentication\LogonUI"
oReg.GetStringValue HKEY_LOCAL_MACHINE,strKeyPath,strValue,strUSER
    if isnull(strUser) then
      strValue = "DefaultUserName"
      strKeyPath = "SOFTWARE\Microsoft\Windows NT\CurrentVersion\WinLogon"
      oReg.GetStringValue HKEY_LOCAL_MACHINE,strKeyPath,strValue,strUSER
    End if 
    if isnull(strUser) then
      strUSER = "KeyNotPresent"
    End if

' Output
objFile.WriteLine ""
objFile.WriteLine "Description,EngineVersion,ASSigVersion,ASSigApplied,AVSigVersion,AVSigApplied,LastScan"
objFile.WriteLine "AV Result," & EngineVersion & "," & ASSigVersion & "," & ASSigTime & "," & AVSigVersion & "," & AVSigTime & "," & LastRunTime
objFile.WriteLine "Description,Manufacturer,Model,SerialNumber,AssetTag,CPU,Ram,HardDisk"
objFile.WriteLine "Inventory," & strManufacturer & "," & strModel & "," & strSerial & "," & strAsset & "," & StrCPU & "," & IntRam & "," & IntDisk
objFile.WriteLine "LastLoggedOnUser," & strUSER
objFile.WriteLine "ScriptVersion," & strVersion
objFile.Close

' Check if we are connected to a domain. This is a preliminary test for transport method.
' It does not explicitly determine the method as the repository my be offline during
' the actual transfer.
If localDebug = 0 Then
    On Error Resume Next
    Set objRootDSE = GetObject("LDAP://" & domainName & "/RootDSE")
    If Err.number = 0 And objFSO.FileExists(fileName) Then
        tSMB()
    Else
        tRemote()    
    End If
Else
    tLocal()
End If
