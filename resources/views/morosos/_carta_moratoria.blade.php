@php
    $nombre = strtoupper($cliente->NOMBRE ?? $cliente->nombre ?? '');
    $domicilio = strtoupper($cliente->CALLE ?? $cliente->calle ?? '');
@endphp

<style>
    .titulo {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
    }

    .telefono {
        text-align: center;
        font-size: 10px;
        font-weight: bold;
    }

    .moratoria {
        text-align: center;
        font-size: 21px;
        font-weight: bold;
        font-style: italic;
        letter-spacing: 1px;
    }

    .linea {
        border-top: 2px solid #000;
        height: 1px;
    }

    .fecha {
        text-align: right;
        font-size: 9.5px;
    }

    .label {
        font-size: 9.5px;
        font-weight: bold;
    }

    .dato {
        font-size: 9.5px;
        border-bottom: 1px dotted #000;
    }

    .texto {
        font-size: 9.5px;
        line-height: 1.35;
        text-align: justify;
    }

    .notificado {
        font-size: 9.5px;
        font-weight: bold;
    }
</style>

<div class="titulo">
    ESTUDIO DE COBRANZAS PREJURIDICAS DR PUJOL
</div>

<div class="telefono">
    TELEF 3865 386766
</div>

<br>

<div class="moratoria">
    UNICA MORATORIA TARJETA PREMIER
</div>

<br>

<div class="linea"></div>

<br>

<div class="fecha">
    Aguilares, ........ de ........................ de 2026
</div>

<br>

<div class="label">Sr/a:</div>
<div class="dato">
    {{ $nombre }}
</div>

<br>

<div class="label">Domicilio:</div>
<div class="dato">
    {{ $domicilio }}
</div>

<br>

<div class="texto">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Como apoderado de TARJETA PREMIER le informo que acogiéndose por
    UNICA VEZ a esta MORATORIA hasta el 30/06/2026, Ud. podrá pagar
    su deuda con una quita de hasta 50% de los intereses.
    <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    COMO SEGUNDO Y UNICO PLAZO hasta el 10/07/2025 la reducción será
    de hasta un 30%.
    <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Para acceder a la misma debe enviarme hasta esa fecha
    “SOLICITO MORATORIA” y su numero de DNI por WhatsApp.
    <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Pasado dicho plazo iniciaremos inmediatamente las Acciones Judiciales
    pertinentes, lo que generará, además de gastos Jurídicos, la Afectación
    de Firmas y embargos de sueldos y/o bienes tanto a Ud. como a su Garante.
</div>

<br>

<div class="notificado">
    Queda usted debidamente notificado.
</div>