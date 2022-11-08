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
    CONSTRAINT C_Blog_PK_01 PRIMARY KEY (blogID),
    CONSTRAINT C_Blog_FK_01 FOREIGN KEY (publisher) REFERENCES User(username)
);

CREATE TABLE Cocktail(
    cocktailID INT NOT NULL AUTO_INCREMENT,
    cocktailName VARCHAR (64),
    publisher VARCHAR (128),
    instructions VARCHAR (2048),
    imageRef VARCHAR (512),
    rating FLOAT (2,1), 
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
    cocktailID INT,
    publisher VARCHAR (128),
    rating INT,
    publishDate DATETIME,
    title VARCHAR (64),
    textBody VARCHAR (2048),
    CONSTRAINT C_CocktailReview_PK_01 PRIMARY KEY (cocktailID, publisher),
    CONSTRAINT C_CocktailReview_FK_01 FOREIGN KEY (cocktailID) REFERENCES Cocktail(cocktailID),
    CONSTRAINT C_CocktailReview_FK_02 FOREIGN KEY (publisher) REFERENCES User(username)
);

CREATE TABLE Ingredient(
    ingredientName VARCHAR (64) PRIMARY KEY,
    CONSTRAINT C_ingredient_U_01 UNIQUE (ingredientName)
);

CREATE TABLE Recipe(
    cocktailID INT,
    ingredientName VARCHAR (64),
    ingredientMeasurement VARCHAR (64),
    CONSTRAINT C_Recipe_PK_01 PRIMARY KEY (cocktailID, ingredientName),
    CONSTRAINT C_Recipe_FK_01 FOREIGN KEY (cocktailID) REFERENCES Cocktail(cocktailID),
    CONSTRAINT C_Recipe_FK_02 FOREIGN KEY (ingredientName) REFERENCES Ingredient(ingredientName)
);

CREATE TABLE IngredientCabinet(
    username VARCHAR (128),
    ingredientName VARCHAR (64),
    CONSTRAINT C_ingredientCabinet_PK_01 PRIMARY KEY (username, ingredientName),
    CONSTRAINT C_ingredientCabinet_FK_01 FOREIGN KEY (username) REFERENCES User(username),
    CONSTRAINT C_ingredientCabinet_FK_02 FOREIGN KEY (ingredientName) REFERENCES Ingredient(ingredientName)
);

INSERT INTO User VALUES ('TheCocktailDB', '3def6c72ec2b07154029c970539988c8bc59c48daf898d4a696641b6b5c7987f');

CREATE DATABASE Miscellaneous;

USE Miscellaneous;

CREATE TABLE SearchTerms(
    searchTerm VARCHAR (128) PRIMARY KEY,
    TTL DATETIME
);