<?php

/**
 * Basisklasse für alle Approvio-spezifischen Exceptions
 */
class ApprovioException extends Exception {}

/**
 * Exception, wenn eine Ressource nicht gefunden wurde
 */
class NotFoundException extends ApprovioException {}

/**
 * Exception, wenn keine Berechtigung vorliegt
 */
class UnauthorizedException extends ApprovioException {}

/**
 * Exception bei doppelter Abstimmung
 */
class DuplicateVoteException extends ApprovioException {}

/**
 * Exception bei doppeltem Benutzer
 */
class DuplicateUserException extends ApprovioException {}

/**
 * Exception bei ungültigen Anmeldedaten
 */
class InvalidCredentialsException extends ApprovioException {}

/**
 * Exception bei inaktivem Benutzer
 */
class InactiveUserException extends ApprovioException {}

/**
 * Exception bei ungültiger Operation
 */
class InvalidOperationException extends ApprovioException {}

/**
 * Exception beim Datei-Upload
 */
class UploadException extends ApprovioException {}

/**
 * Exception bei zu großer Datei
 */
class FileSizeTooLargeException extends ApprovioException {}

/**
 * Exception bei ungültigem Dateityp
 */
class InvalidFileTypeException extends ApprovioException {}
