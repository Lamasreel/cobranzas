import { Metadata } from "next";

export const metadata: Metadata = {
  title: "Política de Privacidad",
  description: "Política de Privacidad del Sistema de Cobranzas",
};

export default function PrivacyPolicy() {
  return (
    <main className="max-w-5xl mx-auto px-6 py-12">
      <h1 className="text-4xl font-bold mb-8">Política de Privacidad</h1>

      <p className="mb-6">
        Última actualización: 23 de julio de 2026
      </p>

      <p className="mb-6">
        Esta Política de Privacidad describe cómo nuestro Sistema de Cobranzas
        recopila, utiliza y protege la información personal de los usuarios y
        clientes que interactúan con nuestros servicios, incluyendo las
        comunicaciones realizadas mediante WhatsApp Business Platform de Meta.
      </p>

      <h2 className="text-2xl font-semibold mt-8 mb-3">
        Información que recopilamos
      </h2>

      <ul className="list-disc ml-6 space-y-2">
        <li>Nombre y apellido.</li>
        <li>Número de documento.</li>
        <li>Número de teléfono.</li>
        <li>Correo electrónico.</li>
        <li>Domicilio.</li>
        <li>Información relacionada con cuentas, cuotas y pagos.</li>
        <li>Historial de comunicaciones.</li>
      </ul>

      <h2 className="text-2xl font-semibold mt-8 mb-3">
        Uso de la información
      </h2>

      <p>
        La información recopilada se utiliza exclusivamente para:
      </p>

      <ul className="list-disc ml-6 mt-3 space-y-2">
        <li>Administrar cuentas de clientes.</li>
        <li>Registrar pagos.</li>
        <li>Enviar recordatorios de vencimiento.</li>
        <li>Enviar avisos de deuda.</li>
        <li>Responder consultas.</li>
        <li>Cumplir obligaciones legales.</li>
      </ul>

      <h2 className="text-2xl font-semibold mt-8 mb-3">
        Uso de WhatsApp
      </h2>

      <p>
        Este sistema utiliza WhatsApp Business Platform de Meta para enviar
        notificaciones relacionadas con el estado de cuentas, vencimientos,
        recordatorios de pago y otras comunicaciones administrativas.
      </p>

      <p className="mt-3">
        No utilizamos WhatsApp para enviar publicidad no solicitada ni
        compartimos los datos personales con terceros para fines comerciales.
      </p>

      <h2 className="text-2xl font-semibold mt-8 mb-3">
        Compartición de datos
      </h2>

      <p>
        Los datos personales únicamente podrán ser compartidos con proveedores
        tecnológicos necesarios para el funcionamiento del sistema, tales como
        Meta Platforms, Inc., proveedores de infraestructura en la nube,
        servicios de correo electrónico o pasarelas de pago, cuando sea
        estrictamente necesario para prestar el servicio o cumplir con
        obligaciones legales.
      </p>

      <h2 className="text-2xl font-semibold mt-8 mb-3">
        Seguridad
      </h2>

      <p>
        Implementamos medidas técnicas y organizativas para proteger la
        información contra accesos no autorizados, pérdida, alteración o uso
        indebido.
      </p>

      <h2 className="text-2xl font-semibold mt-8 mb-3">
        Derechos del usuario
      </h2>

      <p>
        Los usuarios pueden solicitar el acceso, actualización, rectificación o
        eliminación de sus datos personales comunicándose mediante los datos de
        contacto indicados más abajo.
      </p>

      <h2 className="text-2xl font-semibold mt-8 mb-3">
        Contacto
      </h2>

      <p>
        Correo electrónico: contacto@tudominio.com
      </p>

      <p>
        Si tiene consultas sobre esta Política de Privacidad o sobre el
        tratamiento de sus datos personales, puede comunicarse con nosotros a
        través del correo electrónico indicado.
      </p>
    </main>
  );
}