if (typeof mHLogin.i18n === 'undefined') {
    mHLogin.i18n = {
        languages: uHLogin.languages,
        get: function (tag) {
            return typeof this.data[tag] !== 'undefined' ? this.data[tag] : tag;
        },
        data: {
            data_send: 'Datos están enviados',
            data_not_send: 'Datos NO están enviados',
            password: 'Contraseña',
            input_password: '&ge;6 dígitos y caracteres latinos',
            password_repeat: 'Repetir contraseña',
            submit: 'Enviar',
            sign_in: 'Entrar',
            enter: 'Entrada',
            registration_title: 'Registración',
            phone: 'Número de teléfono',
            name: 'Nombre',
            surname: 'Apellido',
            address: 'Dirección',
            promocode: 'Código promocional',
            familar: 'Estoy familiarizado con',
            terms: 'Las reglas del uso',
            conditions: 'acepto todas las condiciones',
            privacy_policy: 'La política de privacidad',
            generate_password: 'Generar contraseña',
            and: 'y',
            on_email: 'Al correo electrónico',
            send_password: 'Recuperación de contraseña',
            forgot_password: '¿Ha olvidado usted la contraseña?',
            show_password: 'Mostrar contraseña',
            hide_password: 'Ocultar contraseña',
            remember_login: 'Recordar entrada',
            close: 'Cerrar',
            mess_password: 'La contraseña será enviada al correo electrónico',
            new_password: 'Nueva contraseña',
            old_password: 'Contraseña actual',
            u_error: 'Se ha producido un error',
            change_password: 'Cambiar contraseña',
            save_changes: 'Guardar cambios',
            error_change_password: 'El enlace está desactualizado o la contraseña ya se ha cambiado.',
            profile: 'Perfil',
            exit: 'Salir',
            error_empty_data: 'Margen obligatorio no está completado',
            error_pattern_data: 'Formato incorrecto',
            error_empty_checkbox: 'Hay que estar aceptar',
            error_password_mismatch: 'Las contraseñas no coinciden',
            error_password_same: 'La contraseña actual y la nueva no deben coincidir',
            waiting: 'Espera...',
            subscription: 'Suscribirse al alerta de noticias',
            will_be_available: 'El reenvío está disponible dentro de',
            seconds: 'segundas',
            resend_password: 'Reenviar contraseña',
            email_confirmed: '¡Gracias, dirección de correo electrónico está confirmada con éxito!',
            email_not_confirmed: 'Para realizar la acción, debe confirmar la dirección del correo electrónico siguiendo el enlace del correo de registración',
            email_confirm_post: 'Reenviar el correo',
            to_main_page: 'A la página inicial',
            redirection_: 'Espera...',
            check_email: 'Confirmo la ortografía correcta y la existencia de la dirección del correo electrónico',
            adminzone_enter: 'Entrar en la zona de administración',
            contact_send_message: 'Comentarios',
            sender_email: 'Correo electrónico del remitente',
            sender_name: 'Nombre del remitente',
            contact_text: 'Mensaje de texto',
            contact: 'Enviar un mensaje',
            enter_reg: 'Entrada/Registración',
            captcha_code: 'Código de captcha',
            sender_mail: 'Mensaje enviado',
            exit_all: 'Cerrar sesión en todos los dispositivos'
        }
    }
}