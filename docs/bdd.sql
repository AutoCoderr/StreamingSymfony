DROP DATABASE IF EXISTS streamingSymfony;
CREATE DATABASE streamingSymfony;
use streamingSymfony

CREATE TABLE Users (
	idUser int(2) NOT NULL AUTO_INCREMENT,
	emailUser varchar(20) NOT NULL,
	prenomUser varchar(20) NOT NULL,
	nomUser varchar(20) NOT NULL,
	password char(60) NOT NULL,
	dateNaissanceUser date NOT NULL,
	perm varchar(10) NOT NULL,
	PRIMARY KEY (idUser)
);

CREATE TABLE Categories (
	idCategorie int(2) NOT NULL AUTO_INCREMENT,
	nomCategorie varchar(20) NOT NULL,
	PRIMARY KEY (idCategorie)
);

CREATE TABLE Sagas (
	idSaga int(2) NOT NULL AUTO_INCREMENT,
	User int(2) NOT NULL,
	Categorie int(2) NOT NULL,
	nomSaga varchar(20) NOT NULL,
	synopsisSaga varchar(500) NOT NULL,
	prenomAuteurSaga varchar(20) NOT NULL,
	nomAuteurSaga varchar(20) NOT NULL,
	imageSaga varchar(30) NOT NULL,
	PRIMARY KEY (idSaga),
	FOREIGN KEY (User) references Users(idUser),
	FOREIGN KEY (Categorie) references Categories(idCategorie)
);

CREATE TABLE Series (
	idSerie int(2) NOT NULL AUTO_INCREMENT,
	User int(2) NOT NULL,
	Categorie int(2) NOT NULL,
	nomSerie varchar(20) NOT NULL,
	synopsisSerie varchar(20) NOT NULL,
	prenomAuteurSerie varchar(20) NOT NULL,
	nomAuteurSerie varchar(20) NOT NULL,
	imageSerie varchar(30) NOT NULL,
	PRIMARY KEY (idSerie),
	FOREIGN KEY (User) references Users(idUser),
	FOREIGN KEY (Categorie) references Categories(idCategorie)
);

CREATE TABLE Films (
	idFilm int(2) NOT NULL AUTO_INCREMENT,
	Saga int(2) NULL,
	Categorie int(2) NULL,
	User int(2) NOT NULL,
	titreFilm varchar(20) NOT NULL,
	dureeFilm time NOT NULL,
	synopsisFilm varchar(500) NOT NULL,
	dateSortieFilm date NOT NULL,
	prenomAuteurFilm varchar(20) NULL,
	nomAuteurFilm varchar(20) NULL,
	imageFilm varchar(30) NOT NULL,
	PRIMARY KEY (idFilm),
	FOREIGN KEY (User) references Users(idUser),
	FOREIGN KEY (Saga) references Sagas(idSaga),
	FOREIGN KEY (Categorie) references Categories(idCategorie)
);

CREATE TABLE Episodes (
	idEpisode int(2) NOT NULL AUTO_INCREMENT,
	Serie int(2) NOT NULL,
	User int(2) NOT NULL,
	titreEpisode varchar(20) NOT NULL,
	dureeEpisode time NOT NULL,
	synopsisEpisode varchar(500) NOT NULL,
	dateSortieEpisode date NOT NULL,
	PRIMARY KEY (idEpisode),
	FOREIGN KEY (Serie) references Series(idSerie),
	FOREIGN KEY (User) references Users(idUser)
);

CREATE TABLE URLs (
	idUrl int(2) NOT NULL AUTO_INCREMENT,
	Film int(2) NULL,
	Episode int(2) NULL,
	lien varchar(128) NOT NULL,
	PRIMARY KEY (idUrl),
	FOREIGN KEY (Film) references Films(idFilm),
	FOREIGN KEY (Episode) references Episodes(idEpisode)
);