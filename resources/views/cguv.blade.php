<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CGUV</title>
    <style>
        body { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif; background: #050505; color: #e5e7eb; }
        .container { max-width: 860px; margin: 0 auto; padding: 32px 20px 64px; }
        .card { background: #0b0b0b; border: 1px solid #1f2937; border-radius: 16px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        h1 { font-size: 28px; margin: 0 0 12px; color: #fff; }
        h2 { font-size: 18px; margin: 20px 0 8px; color: #fff; }
        p, li { font-size: 14px; line-height: 1.6; color: #cbd5f5; }
        ul { padding-left: 18px; margin: 8px 0; }
        .btn { display: inline-block; margin-top: 24px; padding: 10px 18px; border-radius: 9999px; color: #fff; text-decoration: none; font-weight: 600; background: linear-gradient(90deg, #ec4899, #f43f5e, #f97316); }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 12px; background: #111827; border: 1px solid #1f2937; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <span class="badge">Dernière mise à jour : 16/01/2026</span>
            <h1>Conditions Générales d’Utilisation et de Vente (CGUV)</h1>

            <p>Les présentes CGUV encadrent l’accès et l’utilisation du service proposé sur ce site. En créant un compte, vous acceptez sans réserve ces conditions.</p>

            <h2>1. Objet</h2>
            <p>Le site met à disposition des contenus et services numériques accessibles selon les modalités décrites ci-dessous.</p>

            <h2>2. Accès au service</h2>
            <ul>
                <li>Le service est réservé aux personnes majeures.</li>
                <li>Vous vous engagez à fournir des informations exactes lors de votre inscription.</li>
            </ul>

            <h2>3. Compte utilisateur</h2>
            <p>Vous êtes responsable de la confidentialité de vos identifiants et de l’ensemble des actions effectuées depuis votre compte.</p>

            <h2>4. Contenus</h2>
            <p>Les contenus publiés restent la propriété de leurs auteurs. Toute reproduction ou diffusion non autorisée est interdite.</p>

            <h2>5. Tarifs et achats</h2>
            <p>Lorsque des achats sont proposés, les prix sont indiqués en euros, toutes taxes comprises. Les modalités de paiement sont précisées avant validation.</p>

            <h2>6. Droit de rétractation</h2>
            <p>Conformément à la réglementation, l’accès immédiat à un contenu numérique peut entraîner la perte du droit de rétractation.</p>

            <h2>7. Responsabilités</h2>
            <p>Le service est fourni “en l’état”. Nous ne saurions être tenus responsables des interruptions ou dysfonctionnements temporaires.</p>

            <h2>8. Résiliation</h2>
            <p>Vous pouvez supprimer votre compte à tout moment. Nous nous réservons le droit de suspendre un compte en cas de violation des présentes CGUV.</p>

            <h2>9. Droit applicable</h2>
            <p>Les présentes CGUV sont soumises au droit français. Tout litige sera soumis aux tribunaux compétents.</p>

            <a class="btn" href="{{ route('home', ['modal' => 'rencontre']) }}">Retour</a>
        </div>
    </div>
</body>
</html>

