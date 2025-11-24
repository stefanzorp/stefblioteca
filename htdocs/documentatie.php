<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Documentație Proiect Biblioteca Online (Budau Stefan 243)</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f8;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        header {
            text-align: center;
            padding: 20px 0;
            background-color: #2c3e50;
            color: white;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        header h1 { font-size: 2em; }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2c3e50;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }

        p { margin-bottom: 15px; }

        ul { margin-left: 20px; margin-bottom: 15px; }

        li { margin-bottom: 8px; }

        a { color: #3498db; text-decoration: none; }

        a:hover { text-decoration: underline; }

        .section { margin-bottom: 30px; }
    </style>
</head>
<body>

<header>
    <h1>Documentație Proiect: Stefblioteca (biblioteca online)</h1>
</header>

<div class="container">

    <div class="section">
        <h2>1. Prezentarea aplicației</h2>
        <p>Aplicația web "Stefblioteca" permite gestionarea unei biblioteci prin intermediul internetului. Utilizatorii pot:</p>
        <ul>
            <li>Vizualiza lista de cărți disponibile</li>
            <li>Împrumuta sau rezerva cărți</li>
            <li>Lăsa recenzii pentru cărți</li>
            <li>Administra utilizatorii, cărțile și categoriile (rol admin)</li>
            <li>Gestiona împrumuturile și rezervările (rol bibliotecar)</li>
        </ul>
    </div>

    <div class="section">
        <h2>2. Arhitectura aplicației</h2>
        <p>Aplicația este construită pe **PHP + MySQL**, având o arhitectură tip client-server. Fluxul general:</p>
        <ul>
            <li>Clientul accesează paginile web prin browser</li>
            <li>Serverul web (PHP) procesează cererile și interacționează cu baza de date MySQL</li>
            <li>Rezultatele sunt returnate clientului sub formă HTML/CSS</li>
        </ul>

        <h3>2.1. Rolurile utilizatorilor</h3>
        <ul>
            <li><strong>Admin:</strong> gestionează utilizatorii, cărțile și categoriile</li>
            <li><strong>Bibliotecar:</strong> gestionează împrumuturile și rezervările</li>
            <li><strong>Cititor:</strong> vizualizează cărțile, poate face rezervări și lăsa recenzii</li>
        </ul>

        <h3>2.2. Entități și relații</h3>
        <ul>
            <li><strong>Utilizatori:</strong> id, nume, email, rol, status</li>
            <li><strong>Autori:</strong> id, nume, naționalitate, descriere</li>
            <li><strong>Categorii:</strong> id, nume, descriere</li>
            <li><strong>Cărți:</strong> titlu, autor, categorie, ISBN, editura, an_publicare, nr_exemplare, descriere</li>
            <li><strong>Împrumuturi:</strong> legătura între utilizatori și cărți, data împrumutului și data returnării</li>
            <li><strong>Rezervări:</strong> legătura între utilizatori și cărți, status rezervare</li>
            <li><strong>Recenzii:</strong> rating și comentariu pentru fiecare carte</li>
        </ul>

        <h3>2.3. Procese principale</h3>
        <ul>
            <li>Autentificare și autorizare</li>
            <li>Vizualizarea și filtrarea cărților</li>
            <li>Împrumutul și returnarea cărților</li>
            <li>Rezervarea cărților</li>
            <li>Adăugarea de recenzii și evaluări</li>
        </ul>
    </div>

    <div class="section">
        <h2>3. Componente principale</h2>
        <ul>
            <li><strong>index.php:</strong> pagina principală cu lista cărților</li>
            <li><strong>carti.php:</strong> pagina cu detalii despre o carte</li>
            <li><strong>login.php / register.php:</strong> autentificare și înregistrare utilizatori</li>
            <li><strong>includes/config.php:</strong> fișierul de conexiune la baza de date</li>
            <li><strong>assets/:</strong> fișiere CSS, JS și imagini pentru interfață</li>
        </ul>
    </div>

    <div class="section">
        <h2>4. Descrierea bazei de date</h2>
        <p>Baza de date MySQL conține tabelele principale:</p>
        <ul>
            <li><strong>utilizatori</strong> (id, nume, email, parola, rol, status)</li>
            <li><strong>autori</strong> (id, nume, nationalitate, data_nastere, descriere)</li>
            <li><strong>categorii</strong> (id, nume_categorie, descriere)</li>
            <li><strong>carti</strong> (id, titlu, id_autor, id_categorie, ISBN, editura, an_publicare, nr_exemplare, descriere)</li>
            <li><strong>imprumuturi</strong> (id, id_utilizator, id_carte, data_imprumut, data_returnare, status)</li>
            <li><strong>rezervari</strong> (id, id_utilizator, id_carte, data_rezervare, status)</li>
            <li><strong>recenzii</strong> (id, id_utilizator, id_carte, rating, comentariu, data_recenzie)</li>
        </ul>
    </div>

    <div class="section">
        <h2>5. Soluția de implementare</h2>
        <p>Implementarea se face folosind PHP pentru partea de server și MySQL pentru gestionarea datelor. Fluxurile principale includ:</p>
        <ul>
            <li>Login/Logout și verificarea rolului</li>
            <li>Interogări pentru vizualizarea și filtrarea cărților</li>
            <li>Interacțiuni pentru rezervări și împrumuturi</li>
            <li>Introducerea recenziilor și afișarea rating-urilor</li>
        </ul>
    </div>

    <div class="section">
        <h2>Diagrama UML a fluxului de rezervare și împrumut</h2>
        <p>Diagrama de secvență a aplicației web pentru gestionarea rezervărilor și împrumuturilor:</p>
        <img src="assets/img/diagrama.png" alt="Diagrama UML rezervare și împrumut" style="max-width:100%; height:auto; border:1px solid #ccc; padding:5px;">
    </div>

    <div class="section">
        <h2>Diagrama relațiilor tabelelor (ERD)</h2>
        <p>Schema bazei de date MySQL pentru aplicația Biblioteca Online:</p>
        <img src="assets/img/uml_erd.png" alt="Diagrama ERD a bazei de date" style="max-width:100%; height:auto; border:1px solid #ccc; padding:5px;">
    </div>


                
    <div class="section">
        <p>Înapoi la <a href="index.php">pagina principală</a>.</p>
    </div>

</div>

</body>
</html>
