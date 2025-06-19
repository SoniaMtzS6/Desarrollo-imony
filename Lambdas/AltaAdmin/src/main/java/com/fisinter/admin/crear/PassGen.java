package com.fisinter.admin.crear;

import java.security.SecureRandom;

public class PassGen {

    // Definir los caracteres permitidos para la contraseña
    private static final String UPPER_CASE = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private static final String LOWER_CASE = "abcdefghijklmnopqrstuvwxyz";
    private static final String DIGITS = "0123456789";
    private static final String SPECIAL_CHARS = "@#$%&*-_,.";

    // Combinar todas las posibles opciones
    private static final String ALL_CHARS = UPPER_CASE + LOWER_CASE + DIGITS + SPECIAL_CHARS;

    private static SecureRandom random = new SecureRandom();

    public String generatePassword() {

        int length = 8;
        StringBuilder password = new StringBuilder(length);

        // Asegurarse de incluir al menos un carácter de cada tipo
        password.append(UPPER_CASE.charAt(random.nextInt(UPPER_CASE.length())));
        password.append(LOWER_CASE.charAt(random.nextInt(LOWER_CASE.length())));
        password.append(DIGITS.charAt(random.nextInt(DIGITS.length())));
        password.append(SPECIAL_CHARS.charAt(random.nextInt(SPECIAL_CHARS.length())));

        // Rellenar el resto de la contraseña con caracteres aleatorios
        for (int i = 4; i < length; i++) {
            password.append(ALL_CHARS.charAt(random.nextInt(ALL_CHARS.length())));
        }

        // Mezclar los caracteres para que no siga un patrón predecible
        return shuffleString(password.toString());
    }

    // Método para mezclar los caracteres de la contraseña
    private static String shuffleString(String input) {
        StringBuilder result = new StringBuilder(input.length());
        char[] characters = input.toCharArray();

        for (int i = characters.length - 1; i >= 0; i--) {
            int j = random.nextInt(i + 1);
            // Intercambiar caracteres
            char temp = characters[i];
            characters[i] = characters[j];
            characters[j] = temp;
        }

        return new String(characters);
    }
}
