<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Contract de intermediere imobiliară la închiriere</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; margin: 40px; }
        h2 { text-align: center; text-transform: uppercase; margin-bottom: 20px; }
        .header, .footer { text-align: center; font-size: 10px; color: #555; }
        .section { margin-bottom: 20px; }
        .section p { margin: 4px 0; }
        .label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        td, th { padding: 4px; vertical-align: top; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
        .signatures .box { text-align: center; width: 45%; }
        .signatures img { display: block; margin: 10px auto; max-width: 200px; }
        .annex { margin-top: 40px; page-break-before: always; }
    </style>
</head>
<body>

<div class="header">
    <p>REALHUB SRL – APROAPE ACASĂ</p>
    <p>Bd. Mihail Kogălniceanu 17, Bl. C4, Ap.14, Brașov &bull; CUI 51112963 &bull; J20/2500/20XX</p>
    <p>office@aproapeacasa.ro</p>
    <hr>
</div>

<h2>Contract de intermediere imobiliară la închiriere</h2>

<div class="section">
    <p><span class="label">Seria:</span> {{ $fields['serie_contract'] ?? '__________' }}</p>
    <p><span class="label">Nr.:</span> {{ $fields['numar_contract'] ?? '__________' }}</p>
    <p><span class="label">Data:</span> {{ $fields['data_contract'] ?? '__________' }}</p>
</div>

<div class="section">
    <h3>I. Părțile contractului</h3>
    <p><span class="label">Prestator:</span> {{ $fields['company_name'] ?? 'SC REALHUB SRL – APROAPE ACASĂ' }},
        sediul: {{ $fields['company_address'] ?? 'Bd. Mihail Kogălniceanu 17, Bl. C4, Ap.14, Brașov' }},
        CUI: {{ $fields['company_cui'] ?? '51112963' }},
        J: {{ $fields['company_reg'] ?? 'J20/2500/20XX' }},
        email: {{ $fields['company_email'] ?? 'office@aproapeacasa.ro' }},
        reprezentat prin Agent: {{ $fields['agent_name'] ?? '_________________________' }}.</p>

    <p><span class="label">Beneficiar:</span> {{ $fields['client_name'] ?? '_________________________' }},
        CNP: {{ $fields['cnp'] ?? '_________' }},
        act de identitate: {{ $fields['id_series'] ?? '___' }} seria {{ $fields['id_series'] ?? '___' }},
        nr. {{ $fields['id_number'] ?? '________' }},
        domiciliat(ă): {{ $fields['client_address'] ?? '_________________________' }},
        telefon: {{ $fields['phone'] ?? '_________' }},
        email: {{ $fields['email'] ?? '_________' }},
        calitate: {{ $fields['client_role'] ?? '_________________' }}.</p>
</div>

<div class="section">
    <h3>II. Obiectul contractului</h3>
    <p>2.1. Prezentul contract are ca obiect prestarea serviciilor de intermediere imobiliară de către Prestator pentru Beneficiar în
        scopul închierii Imobilului identificat la art. 3 de mai jos</p>
    <p>2.2. Finalitatea avută în vedere de Beneficiar este identificarea de către Prestator a unui Potențial Chiriaș interesat de
        Oferta imobiliară a Beneficiarului și semnarea unei Tranzacții Imobiliare (adică orice acord de voință dintre Beneficiar și terț
        privind închirierea imobilului - contract de închiriere).</p>
    <p>2.3. În vederea realizării obiectului Contractului, Beneficiarul împuternicește Prestatorul să îl reprezinte în procesul de
        închiriere a Imobilului pentru: promovarea Imobilului, efectuarea de vizionări, negocierea cu terții interesați de Oferta imobiliară
        a Beneficiarului în limitele de preț prevăzute în prezentul contract
    </p>
</div>

<div class="section">
    <h3>III. Oferta imobiliară a Beneficiarului</h3>
    <table>
        <tr><td class="label">Tip imobil:</td><td>{{ $fields['tip_imobil'] ?? '_________' }}</td></tr>
        <tr><td class="label">Adresa:</td><td>{{ $fields['property_address'] ?? '_________' }}</td></tr>
        <tr><td class="label">Preț închiriere:</td><td>{{ $fields['price'] ?? '_________' }} lei</td></tr>
        <tr><td class="label">Negociabil:</td><td>{{ $fields['negotiable'] ?? '_________' }}</td></tr>
    </table>
    <p>3.2. Beneficiarul declară că informațiile din Fişa Imobilului (Anexa 1) sunt reale și complete și se obligă să anunțe orice modificare.</p>
</div>

<div class="section">
    <h3>IV. Durata contractului</h3>
    <p>4.1. Contractul intră în vigoare la semnarea de către părţi, este pe durată nelimitată și poate fi denunțat cu 10 zile preaviz.</p>
</div>

<div class="section">
    <h3>V. Comisionul și cheltuieli</h3>
    <p>5.1. Comision: {{ $fields['mission_price'] ?? '_________' }}% + TVA din prețul de închiriere.</p>
    <p>5.2. Se achită la semnarea contractului de închiriere; întârzierea atrage penalități de 0,5%/zi.</p>
    <p>5.3. Comisionul se plătește în lei la cursul BNR din ziua facturii.</p>
</div>

<div class="section">
    <h3>VI. Drepturile și obligațiile părților </h3>
    <p>6.1. Prestatorul se obligă să promoveze imobilul, să facă vizionări și să negocieze.</p>
    <p>6.2. Beneficiarul se obligă să furnizeze informații reale, să plătească comisionul și să nu negocieze direct cu terţii.</p>
</div>

<div class="section">
    <h3>VII–IX. Răspundere, Încetare, Clauze finale</h3>
    <p>Se aplică clauzele standard privind răspunderea contractuală, încetarea contractului (art. VIII) și dispozițiile finale (art. IX).</p>
</div>

<div class="signatures">
    <div class="box">
        <p>Semnătură Agent:</p>
        @if($contract->signature_agent)
            <img src="data:image/png;base64,{{ $contract->signature_agent }}" alt="Agent Signature">
        @else
            <div style="height:80px; border-bottom:1px solid #000;"></div>
        @endif
    </div>
    <div class="box">
        <p>Semnătură Beneficiar:</p>
        @if($contract->signature_client)
            <img src="data:image/png;base64,{{ $contract->signature_client }}" alt="Client Signature">
        @else
            <div style="height:80px; border-bottom:1px solid #000;"></div>
        @endif
    </div>
</div>

<div class="annex">
    <h3>Anexa 1: Fișa Imobilului</h3>
    <table>
        <tr><td class="label">Nr. CF:</td><td>{{ $fields['nr_cf'] ?? '_________' }}</td></tr>
        <tr><td class="label">Nr. cadastral:</td><td>{{ $fields['nr_cadastral'] ?? '_________' }}</td></tr>
        <tr><td class="label">Nr. topo:</td><td>{{ $fields['nr_topo'] ?? '_________' }}</td></tr>
        <tr><td class="label">Sarcini și ipoteci:</td><td>{{ $fields['sarcini_ipoteci'] ?? '_________' }}</td></tr>
        <tr><td class="label">Litigii:</td><td>{{ $fields['litigii'] ?? '_________' }}</td></tr>
        <tr><td class="label">Vicii ascunse:</td><td>{{ $fields['vicii'] ?? '_________' }}</td></tr>
        <tr><td class="label">Alte caracteristici:</td><td>{{ $fields['alte_caracteristici'] ?? '_________' }}</td></tr>
    </table>
</div>

<div class="footer">
    <p>Pagina {{ $page ?? '' }} din {{ $totalPages ?? '' }}</p>
</div>

</body>
</html>
