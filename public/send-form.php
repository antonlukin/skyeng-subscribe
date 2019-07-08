<?php
/**
 * Subscribe-form handler
 *
 * @author Anton Lukin
 * @version 1.0
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

/**
 * Try to kill non-ajax requests first
 */
if ( empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest' ) {
    http_response_code( '403' );
    exit;
}

/**
 * Single form handler class
 */
class SkyengSubscribe {
    /**
     * Google spreadsheet ID
     *
     * @access  public
     * @var     string
     */
    private static $spreadsheet = '';

    /**
     * Spreadsheet sheet ID
     *
     * @access  private
     * @var     string
     */
    private static $sheet_id = '';

    /**
     * Send json message
     *
     * @access  private
     * @return  void
     *
     * @param   bool  $succces  Request status
     * @param   string  $message  Optional response message
     */
    private static function send_json( $success = true, $message = '' ) {
        header( 'Content-Type: application/json' );

        $response = array( 'success' => $success, 'message' => $message );
        echo json_encode( $response );

        exit;
    }

    /**
     * Get Google Sheets service
     *
     * @access  private
     * @return  object
     */
    private static function get_service() {
        // Add credentials.json path
        $credentials = dirname( __DIR__ ) . '/config/google-credentials.json';
        putenv( 'GOOGLE_APPLICATION_CREDENTIALS=' . $credentials );

        // Create google client
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();

        // Add only spreadsheets scope
        $client->addScope( 'https://www.googleapis.com/auth/spreadsheets' );

        // Create new sheets service
        $service = new Google_Service_Sheets( $client );

        return $service;
    }

    /**
     * Check existsing email
     *
     * @access  private
     * @return  void
     *
     * @param   object  $service  Google_Service_Sheets Instance
     * @param   string  $email  User entered email
     */
    private static function check_existing( $service, $email ) {
        $range = self::$sheet_id . 'A2:C1000';

        try {
            $response = $service->spreadsheets_values->get( self::$spreadsheet, $range );

            if ( ! empty( $response->values ) ) {
                $values = $response->values;

                foreach ( $values as $value ) {
                    if ( isset( $value[0] ) && $value[0] == $email ) {
                        self::send_json( false, 'Кажется, вы уже подписывались на наш курс' );
                    }
                }
            }

        } catch( Exception $e) {
            // We can log $e->getMessage(); here
            self::send_json( false, 'Неожиданная ошибка. Попробуйте позже' );
        }
    }

    /**
     * Check form fields
     *
     * @access  private
     * @return  array
     *
     * @param   array  $fields  Email and name variables
     */
    private static function check_fields( $fields ) {
        if ( empty( $fields['name'] ) || empty( $fields['email'] ) ) {
            self::send_json( false, 'Нужно заполнить оба поля формы' );
        }

        if ( ! filter_var( $fields['email'], FILTER_VALIDATE_EMAIL ) ) {
            self::send_json( false, 'Кажется, вы ввели несуществующий email' );
        }

        return $fields;
    }

    /**
     * Try to add new request
     *
     * @access  private
     * @return  void
     *
     * @param   array  $fields  Email and name variables
     */
    private static function add_request( $fields ) {
        $service = self::get_service();

        // Check if email already exists
        self::check_existing( $service, $fields['email'] );

        $range = self::$sheet_id . 'A2:C1000';

        try {
            $values = new Google_Service_Sheets_ValueRange( [
                'values' => [
                    [ $fields['email'], $fields['name'], date('d.m.Y H:i:s') ]
                ]
            ] );

            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];

            $response = $service->spreadsheets_values->append(self::$spreadsheet, $range, $values, $params);
        } catch( Exception $e) {
            // We can log $e->getMessage(); here
            self::send_json( false, 'Неожиданная ошибка. Попробуйте позже' );
        }

        self::send_json( true );
    }


    /**
     * Init class method
     *
     * @access  public
     * @return  void
     *
     * @param   string  $spreadsheet
     * @param   string  $sheet_id  Sheet page slug
     */
    public static function init( $spreadsheet, $sheet_id = '' ) {
        // Setup spreadsheet ID
        self::$spreadsheet = $spreadsheet;

        // Add spreadsheet sheet id if not empty
        if ( ! empty( $sheet_id ) ) {
            self::$sheet_id = $sheet_id . '!';
        }

        $fields = self::check_fields( $_POST );

        self::add_request( $fields );
    }
}

/**
 * Let's start with spreadsheet ID and empty sheet ID
 */
SkyengSubscribe::init( '1ryVeeICr0Dj-PN8e8v4tSjPl3hz5JeaT6liILysBubM' );