<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso8859-1" />
<title>Exercice</title>
</head>
<body>
<?php
$prix_unitaire = $quantite = $tva = $remise = $livraison = 0;
$prix_ht = $prix_remise = $montant_tva = $prix_ttc = $total = 0;

if($_SERVER["REQUEST_METHOD"] == "POST"  )
{
	$prix_unitaire = $_POST['prix_unitaire'];
	$quantite = $_POST['quantite'];
	$tva =$_POST['tva'] ;
	$remise = $_POST['remise'] ;
	$livraison = $_POST['livraison'] ;
$prix_ht = $prix_unitaire*$quantite;
$prix_remise = number_format($prix_ht-($prix_ht*$remise/100),2);
$montant_tva = number_format($prix_remise*$tva/100,2);
$prix_ttc = number_format($montant_tva+$prix_remise,2);
$total = number_format($prix_ttc + $livraison,2);


}
else
{
echo "<b>Le formulaire est incomplet!</b>";
}

?>	
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<fieldset style="width:350px;">
<legend><b>Calculateur de prix </b></legend>
<table border="0" >
<tr>
	<td><label for="prix_unitaire">Prix unitaire HT :</label></td>
	<td><input type="text" id="prix_unitaire" name="prix_unitaire" value="<?php echo $prix_unitaire; ?>" ></td>
</tr>
<tr>
	<td><label for="quantite">Quantité :</label></td>
	<td><input type="text" id="quantite" name="quantite" value="<?php echo $quantite; ?>" ></td>
</tr>
<tr>
	<td><label for="remise">Taux de remise (%) :</label>
    </td>
    <td><input type="text" id="remise" name="remise" value="<?php echo $remise; ?>" ></td>
</tr>

<tr>
<td>Taux de TVA (en %) : </td>
<td><input type="text" name="tva" value="<?php echo $tva ?>"/></td>
</tr>
<tr>
	<td><label for="livraison">Frais de livraison :</label></td>
	<td><input type="number" id="livraison" name="livraison" value="<?php echo $livraison; ?>" ></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><input type="submit" value="Calculer" /></td>
</tr>

</table>
</fieldset>
<fieldset style="width:350px;">
	<legend><b>Resultat</b></legend>
<table>
<?php

echo "<tr><td>Prix Hors Taxes : </td><td><input type='text' value=$prix_ht readonly/></td></tr>";
echo "<tr><td>Prix avec remise : </td><td><input type='text' value=$prix_remise readonly/> </td></tr> ";
echo "<tr><td>Montant de la TVA : </td><td><input type='text' value=$montant_tva readonly/></td></tr>";
echo "<tr><td>Prix TTC : </td><td><input type='text' value=$prix_ttc readonly/></td></tr>";
echo "<tr><td>Montant total à payer : </td><td><input type='text' value=$total readonly/></td></tr>";

?>
</table>
</fieldset>
</form>

</body>
</html>