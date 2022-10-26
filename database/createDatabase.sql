CREATE DATABASE ProjectNull;

USE ProjectNull;

CREATE TABLE User (
    username VARCHAR (128) PRIMARY KEY,
    passwordHash VARCHAR (128),
    CONSTRAINT C_User_U_01 UNIQUE (username)
);

CREATE TABLE Session (
    sessionID VARCHAR (128) PRIMARY KEY,
    username VARCHAR (128),
    expiration DATETIME,
    CONSTRAINT C_Session_FK_01 FOREIGN KEY (username) REFERENCES User(username)
);

CREATE TABLE Blog (
    blogID INT NOT NULL AUTO_INCREMENT,
    publisher VARCHAR (128),
    publishDate DATETIME,
    title VARCHAR (64),
    textBody VARCHAR (8192),
    CONSTRAINT C_Blog_PK_01 PRIMARY KEY (blogID, publisher),
    CONSTRAINT C_Blog_FK_01 FOREIGN KEY (publisher) REFERENCES User(username)
);

CREATE TABLE Cocktail(
    cocktailID INT NOT NULL AUTO_INCREMENT,
    cocktailName VARCHAR (64),
    publisher VARCHAR (128),
    instructions VARCHAR (2048),
    cocktailImage VARCHAR (512),
    CONSTRAINT C_Cocktail_PK_01 PRIMARY KEY (cocktailID),
    CONSTRAINT C_Cocktail_FK_01 FOREIGN KEY (publisher) REFERENCES User(username)
);

CREATE TABLE LikedCocktail(
    username VARCHAR (128),
    cocktailID INT,
    CONSTRAINT C_LikedCocktail_PK_01 PRIMARY KEY (username, cocktailID),
    CONSTRAINT C_LikedCocktail_FK_01 FOREIGN KEY (username) REFERENCES User(username),
    CONSTRAINT C_LikedCocktail_FK_02 FOREIGN KEY (cocktailID) REFERENCES Cocktail(cocktailID)
);

CREATE TABLE RecipeBlog(
    blogID INT,
    cocktailID INT,
    CONSTRAINT C_RecipeBlog_PK_01 PRIMARY KEY (blogID, cocktailID),
    CONSTRAINT C_RecipeBlog_FK_01 FOREIGN KEY (blogID) REFERENCES Blog(blogID),
    CONSTRAINT C_RecipeBlog_FK_02 FOREIGN KEY (cocktailID) REFERENCES Cocktail(cocktailID)
);

CREATE TABLE CocktailReview(
    cocktailID INT PRIMARY KEY,
    rating FLOAT (2,1),
    textBody VARCHAR (2048),
    CONSTRAINT C_CocktailReview_FK_01 FOREIGN KEY (cocktailID) REFERENCES Cocktail(cocktailID)
);

CREATE TABLE Liquor(
    liquorID VARCHAR (128) PRIMARY KEY,
    liquorName VARCHAR (64),
    liquorImage VARCHAR (512),
    CONSTRAINT C_liquorID_U_01 UNIQUE (liquorID)
);

CREATE TABLE Recipe(
    cocktailID INT,
    ingredientID VARCHAR (128),
    ingredientMeasurement VARCHAR (64),
    CONSTRAINT C_Recipe_PK_01 PRIMARY KEY (cocktailID, ingredientID),
    CONSTRAINT C_Recipe_FK_01 FOREIGN KEY (cocktailID) REFERENCES Cocktail(cocktailID),
    CONSTRAINT C_Recipe_FK_02 FOREIGN KEY (ingredientID) REFERENCES Liquor(liquorID)
);

CREATE TABLE LiquorCabinet(
    username VARCHAR (128),
    liquorID VARCHAR (128),
    CONSTRAINT C_LiquorCabinet_PK_01 PRIMARY KEY (username, liquorID),
    CONSTRAINT C_LiquorCabinet_FK_01 FOREIGN KEY (username) REFERENCES User(username),
    CONSTRAINT C_LiquorCabinet_FK_02 FOREIGN KEY (liquorID) REFERENCES Liquor(liquorID)
);