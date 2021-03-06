<?php

function deleteGenre($idGenre, $conn)
{
	/* Supprime un genre et :
	* - Supprime les occurrences du genre dans les favoris des musiciens
	* - fixe à NULL le genre des groupes au genre.
	*/
	require_once "../data/MyPDO.musiciens-groupes.include.php";

	$suppr_Genre = $conn->prepare(<<<SQL
		DELETE FROM `Genre`
			WHERE `id` = {$idGenre}
SQL
	);

	$suppr_Aime = $conn->prepare(<<<SQL
		DELETE FROM `Aime`
			WHERE `id_genre` = {$idGenre}
SQL
	);

	$modif_Groupe = $conn->prepare(<<<SQL
		UPDATE `Groupe`
			SET `id_genre` = NULL
			WHERE `id_genre` = {$idGenre}
SQL
	);


	$suppr_Genre->execute();
	$suppr_Aime->execute();
	$modif_Groupe->execute();
}

function createGenre($genre, $conn)
{
	/* Crée un genre, avec son nom
	*/

	require_once "../data/MyPDO.musiciens-groupes.include.php";

	$crea_Genre = $conn->prepare(<<<SQL
		INSERT INTO `Genre` (`id`, `nom_genre`)
			VALUES (NULL, "{$genre['nom']}")
SQL
	);

	$crea_Genre->execute();

	$id  = intval( $conn->lastInsertId() );

	$genre['id'] = $id;

	$reponse = array( 'genre' => $genre
	);
	return($reponse);
}

function searchGenre($genre, $conn)
{
	require_once '../data/MyPDO.musiciens-groupes.include.php';

	$req_Genre_JOIN = "";
	$req_Genre_WHERE = "WHERE 1 ";
	$req_Genre_texte = "SELECT DISTINCT g.id AS 'id' FROM `Genre` AS `g`";


	//par nom
	if( isset($genre['nom']) )
	{
		$req_Nom_texte = "AND `nom_genre` LIKE '%".$genre['nom']."%'";
		$req_Genre_WHERE = $req_Genre_WHERE.' '.$req_Nom_texte;
	}

	$req_Genre =$conn->prepare($req_Genre_texte.' '.$req_Genre_WHERE);
	$req_Genre->execute();


	$nbGenres = 0;

	while( ($genre = $req_Genre->fetch()) !== false )
	{
		$resultat[$nbGenres] = selectGenre($genre['id'], $conn);
		$nbGenres++;
	}
	if( $nbGenres === 0)
	{
		$resultat = array('message' => 'Pas de résultats !');
	}
	$resultat['nombre'] = $nbGenres;
	return $resultat;
}

function selectGenre($id, $conn)
{
	require_once '../data/MyPDO.musiciens-groupes.include.php';

	$req = $conn->prepare(<<<SQL
		SELECT g.id AS 'id_genre', g.nom_genre AS 'nom_genre'
		FROM Genre AS g
		WHERE g.id = {$id}
SQL
		);
	$req->execute();


	$resultat['genre'] = array();
	$resultat['genre']['id'] = $id;

	$trouve= false;
	while( ($genre = $req->fetch()) !== false )
	{
		$trouve = true;

		$resultat['genre']['nom'] = $genre['nom_genre'];
	}
	if( $trouve )
		return $resultat;
	else
		return false;
}

function updateGenre($ancien, $genre, $conn)
{
	/* LA VARIABLE $ancien contient les information du genre
	* avant la mise-à-jour, pour comparer avec les nouvelles valeurs.*/
	$id = $ancien['genre']['id'];

	/** Genre **/
	if( isset($genre['nom']) )
	{
		$texte_nom = isset($genre['nom']) ? "`nom_genre` = '{$genre['nom']}'" : "`nom_genre` = '{$ancien['genre']['nom']}'";

		$req_total = <<<SQL
			UPDATE `Genre`
				SET {$texte_nom}
				WHERE`id` = {$id}
SQL
;
		$modif_Genre = $conn->prepare($req_total);
		$modif_Genre->execute();
	}
	/** **/

	$resultat = array('genre' => $genre
				);
	return $resultat;

}




?>