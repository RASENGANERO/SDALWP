<?php

namespace Wpshop\Quizle\Integration;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\NoteType\CommonNote;
use AmoCRM\Models\TagModel;
use AmoCRM\Models\Unsorted\FormsMetadata;
use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Logger;
use Wpshop\Quizle\QuizleResult;

/**
 * @see https://www.amocrm.ru/developers/content/oauth/step-by-step
 */
class AmoCRM {

    use IntegrationTrait;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Settings $settings
     */
    public function __construct( Settings $settings, Logger $logger ) {
        $this->settings = $settings;
        $this->logger   = $logger;
    }

//    public function init() {
//        add_action( 'parse_request', function () {
//            if ( ! isset( $_REQUEST['webhook'] ) ||
//                 $_REQUEST['webhook'] !== 'amocrm'
//            ) {
//                return;
//            }
//            $this->webhook();
//        } );
//    }
//
//    /**
//     * @param array $query_params
//     *
//     * @return string
//     */
    public function get_redirect_url( $query_params = [] ) {
        return add_query_arg( wp_parse_args( [
            'webhook' => 'amocrm',
        ], $query_params ), site_url() );
    }
//
//    /**
//     * @return void
//     */
//    #[NoReturn]
//    protected function webhook() {
//
//    }

    public function get_api_client() {
        $domain = $this->settings->get_value( 'integrations.amocrm.base_domain' );
        $token  = $this->settings->get_value( 'integrations.amocrm.long_term_token' );

        if ( ! $token || ! $domain ) {
            return null;
        }

        $apiClient            = new AmoCRMApiClient();
        $longLivedAccessToken = new LongLivedAccessToken( $token );

        $apiClient
            ->setAccessToken( $longLivedAccessToken )
            ->setAccountBaseDomain( $domain )
        ;

        return $apiClient;
    }

    /**
     * @param array $data
     *
     * @return void
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     *
     * @see https://www.amocrm.ru/developers/content/oauth/step-by-step
     * @see https://www.amocrm.ru/developers/content/crm_platform/duplication-control
     */
    public function submit_result( $data, QuizleResult $result ) {
        if ( ! ( $apiClient = $this->get_api_client() ) ) {
            return;
        }

        $lead = [
            'price'   => $this->settings->get_value( 'integrations.amocrm.price' ),
            'name'    => __( 'User From Quizle', 'quizle' ),
            'contact' => [
                'name'  => $data['username'],
                'phone' => $data['phone'],
                'email' => $data['email'],
            ],
            'tag'     => __( 'New Client', 'quizle' ),
        ];

        /**
         * Allows to modify amocrm lead data
         *
         * @since 1.3
         */
        $lead = apply_filters( 'quizle/integration/amocrm_lead', $lead, $data, $result );

        $leadsCollection = new LeadsCollection();

        $lead = ( new LeadModel() )
            ->setName( $lead['name'] )
            ->setPrice( $lead['price'] )
            ->setTags( ( new TagsCollection() )
                ->add( ( new TagModel() )
                    ->setName( $lead['tag'] )
                )
            )
            ->setContacts( ( new ContactsCollection() )
                ->add( ( new ContactModel() )
                    ->setName( $lead['contact']['name'] )
                    ->setCustomFieldsValues( ( new CustomFieldsValuesCollection() )
                        ->add( ( new MultitextCustomFieldValuesModel() )
                            ->setFieldCode( 'PHONE' )
                            ->setValues( ( new MultitextCustomFieldValueCollection() )
                                ->add( ( new MultitextCustomFieldValueModel() )
                                    ->setValue( $lead['contact']['phone'] )
                                )
                            )
                        )
                        ->add( ( new MultitextCustomFieldValuesModel() )
                            ->setFieldCode( 'EMAIL' )
                            ->setValues( ( new MultitextCustomFieldValueCollection() )
                                ->add( ( new MultitextCustomFieldValueModel() )
                                    ->setValue( $lead['contact']['email'] )
                                )
                            )
                        )
                    )
                )
            )
            ->setMetadata( ( new FormsMetadata() )
                ->setFormId( 'quizle-' . $data['quizle_name'] )
                ->setFormName( 'Quizle: ' . $data['quizle_title'] )
                ->setFormPage( site_url( $result->get_context()->get_relative_url() ) )
                ->setFormSentAt( mktime( date( 'h' ), date( 'i' ), date( 's' ), date( 'm' ), date( 'd' ), date( 'Y' ) ) )
            //->setReferer( 'https://google.com/search' )
            //->setIp( '192.168.0.1' )
            )
        ;


        $leadsCollection->add( $lead );

        try {
            $leadNotesService     = $apiClient->notes( EntityTypesInterface::LEADS );
            $addedLeadsCollection = $apiClient->leads()->addComplex( $leadsCollection );

            $noteText = $this->get_note_text( $result, $data );

            foreach ( $addedLeadsCollection as $addedLead ) {
                $leadId = $addedLead->getId();

                if ( $noteText ) {
                    $notesCollection = new NotesCollection();

                    $note = new CommonNote();
                    $note
                        ->setEntityId( $leadId )
                        ->setText( $noteText )
                    ;

                    $notesCollection->add( $note );
                    $notesCollection = $leadNotesService->add( $notesCollection );
                }
            }
        } catch ( AmoCRMApiException $e ) {
            $this->logger->error( $e );
        }
    }
}
