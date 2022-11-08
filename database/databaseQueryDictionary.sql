--Authenticate Username and Password
--authenticationServer.php, function doLogin(...)
SELECT * FROM User WHERE username = "User Input Username" AND passHash = "User Input Password (System Hashed)" LIMIT 1;

--Store Session Information
--authenticationServer.php, function doLogin(...)
INSERT INTO Session VALUES("Session ID", "Username", "Expiration Date and Time");

--Check Username Availability
--authenticationServer.php, function doRegistration(...)
SELECT * FROM User WHERE username = "User Input Username";

--Insert Username and Password
--authenticationServer.php, function doRegistration(...)
INSERT INTO User VALUES("User Input Username", "User Input Password (System Hashed)");

--Session Validation
--authenticationServer.php, function doSession(...)
SELECT * FROM Session WHERE sessionID = "Cookie Session ID" AND username = "Cookie Username" AND expiration = "Cookie Expires Date/Time" LIMIT 1;