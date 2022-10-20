--Authenticate Username and Password
--webserverCommunications.php, function doLogin(...)
SELECT * FROM User WHERE username = "User Input Username" AND password = "User Input Password (System Hashed)" LIMIT 1;

--Check Username Availability
--webserverCommunications.php, function doRegistration(...)
SELECT * FROM User WHERE username = "User Input Username";

--Insert Username and Password
--webserverCommunications.php, function doRegistration(...)
INSERT INTO User VALUES("User Input Username", "User Input Password (System Hashed)");