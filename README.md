Plan de Proiect: AwA (Actor Awards Visualizer)

Echipa: Dumitrița (Student 1) & Laura (Student 2)

Perioada: 10 Mai 2026 – 23 Mai 2026 (14 Zile)

Atenție (Reguli stricte respectate): 

•	Fără framework-uri pe Front-end (Nu React, Nu Bootstrap). Se va folosi exclusiv HTML5, CSS3 pur și Vanilla JavaScript (AJAX/Fetch).
•	Fără framework-uri pe Back-end (Nu Laravel). Se va folosi PHP nativ.
•	Fără biblioteci de grafice: Pentru a asigura conformitatea totală, cele 3 vizualizări vor fi desenate 100% nativ folosind generare de elemente SVG direct din JavaScript. (Fără Chart.js).
🗓️ Săptămâna 1: Fundația, API-uri și Baza de Date
Zilele 1-2 (10 Mai - 11 Mai)
•	Dumitrița (S1): Proiectarea și crearea bazei de date SQLite. Scrierea scriptului PHP intern pentru popularea/importul datelor SAG (nominalizările pentru ultimii 5 ani).
•	Laura (S2): Crearea layout-ului principal al paginii (Design Responsive, Mobile First) folosind exclusiv CSS Grid și Flexbox. Pregătirea structurii fișierelor HTML/CSS.
Zilele 3-4 (12 Mai - 13 Mai)
•	Dumitrița (S1): Crearea endpoint-ului PHP pentru căutarea și filtrarea listei de actori (livrare JSON). Implementarea logicii AJAX (Vanilla JS) pentru a afișa actorii în interfață fără refresh.
•	Laura (S2): Integrarea back-end cu API-ul extern TMDb folosind PHP cURL pentru a extrage poze și biografii. Afișarea acestor date sub formă de componente dinamice tip "card" pe pagina creată anterior.
Zilele 5-6 (14 Mai - 15 Mai)
•	Dumitrița (S1): Dezvoltarea modulului de Autentificare pentru administratori (utilizare Sesiuni PHP `$_SESSION` și implementare protecții împotriva SQL Injection și XSS).
•	Laura (S2): Construirea endpoint-ului PHP de tip Statistics API (`get_stats.php`) care agregă datele necesare generării graficelor și le trimite către client în format JSON.
🗓️ Săptămâna 2: Vizualizări NATIVE, Știri și Documentație
Zilele 7-8 (16 Mai - 17 Mai)
•	Dumitrița (S1): Implementarea back-end-ului pentru Agregatorul de Știri (parsarea surselor RSS/JSON externe). Crearea panoului de administrare (CRUD via AJAX) pentru configurarea acestor surse de știri.
•	Laura (S2): Dezvoltarea primelor două Vizualizări NATIVE (ex: Bar Chart și Pie Chart). Graficele vor fi construite prin manipularea DOM-ului și generarea de etichete <svg>, <rect>, <circle>, <path> din JavaScript, fără nicio bibliotecă externă.
Zilele 9-10 (18 Mai - 19 Mai)
•	Dumitrița (S1): Finalizarea afișării fluxului de știri pe pagina publică în funcție de actorul selectat. Implementarea funcției back-end de export a datelor brute în format CSV.
•	Laura (S2): Implementarea celei de-a 3-a vizualizări native SVG. Dezvoltarea logicii de descărcare a vizualizărilor în formatele SVG (descărcare directă a sursei DOM) și WebP (desenarea SVG-ului pe un <canvas> ascuns folosind API-ul nativ).
Ziua 11 (20 Mai)
•	Dumitrița & Laura: Testare încrucișată (Cross-Testing). Validarea strictă a codului cu W3C HTML/CSS Validator și repararea eventualelor bug-uri de afișare pe diverse ecrane.
Ziua 12 (21 Mai)
•	Dumitrița & Laura: Redactarea documentației sub formă de Raport Scholarly HTML. Scrierea specificațiilor pe baza șablonului IEEE System Requirements Specification.
Ziua 13 (22 Mai)
•	Dumitrița & Laura: Realizarea arhitecturii de ansamblu folosind Modelul C4. Generarea diagramelor pentru Nivelul 1 (Context) și Nivelul 2 (Containere).
Ziua 14 (23 Mai)
•	Dumitrița & Laura: Înregistrarea și editarea Filmulului Demonstrativ cu durata între 3 și 5 minute la calitate UHD. Pregătirea repository-ului final (fișier README detaliat și menționarea Licenței Libere aplicate codului).
