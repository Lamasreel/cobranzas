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
    .firma {
        text-align: right;
    }

</style>

<?php   $GARANTE = 0;  ?>

<div class="titulo">ESTUDIO DE COBRANZAS PREJURIDICAS DR PUJOL</div>
<div class="telefono">TELEF 3865 386766</div>


<div class="moratoria">UNICA MORATORIA TARJETA PREMIER</div>


<div class="linea"></div>

<br>

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="55%" style="font-size:10px; font-weight:bold;">
            Sr/a:____________________________________________________
        </td>

        <td width="45%" style="font-size:10px; text-align:right;">
            Aguilares, ........ de ........................ de 2026
        </td>
    </tr>
</table>

<br>

<div class="label">Domicilio:____________________________________________________</div>
<br>

<?php if($GARANTE === 1){ ?>
<div class="label">Ud. garantiza solidariamente la Cuenta Vencida en PREMIER del Sr/a:____________________________________</div> 
<?php } ?>

<div class="texto">
    Como apoderado de TARJETA PREMIER le informo que acogiéndose por
    UNICA VEZ a esta MORATORIA hasta el 30/06/2026, Ud. podrá pagar
    su deuda con una quita de hasta 50% de los intereses.
    <br>
    COMO SEGUNDO Y UNICO PLAZO hasta el 10/07/2026 la reducción será
    de hasta un 30%.
    <br>
    Para acceder a la misma debe enviarme hasta esa fecha
    “SOLICITO MORATORIA” y su número de DNI por WhatsApp.
    <br>
    Pasado dicho plazo iniciaremos inmediatamente las Acciones Judiciales
    pertinentes, lo que generará, además de gastos Jurídicos, la Afectación
    de Firmas y embargos de sueldos y/o bienes tanto a Ud. como a su Garante.
</div>

<div class="notificado">
    Queda usted debidamente notificado.
</div>


<div class="firma">
        <img src="{{ base_path('assets/images/firma_abogado.png') }}" width="70">
</div>
