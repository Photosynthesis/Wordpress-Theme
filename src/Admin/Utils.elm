module Admin.Utils exposing
    (  -- Form Submission Notices
       SubmissionStatus(..)

    , adminGet
    , adminPost
    , formLabel
    ,  formRow
       -- Http Utils

    , hasSubmissionError
    , initialSubmissionStatus
    , simpleLabel
    ,  statusFromWebData
       -- Html Utils

    , submissionAwaitingResponse
    , submissionNotice
    , submissionSpinner
    )

{-| Utility Functions for Interacting with the WP Admin Site
-}

import Html exposing (Html, div, label, td, text, th, tr)
import Html.Attributes exposing (class, for)
import Http
import Json.Decode exposing (Decoder, Value)
import RemoteData exposing (WebData)


{-| Enumerate the potential status of a form submission.
-}
type SubmissionStatus
    = NotSent
    | AwaitingResponse
    | ReturnedSuccess
    | ReturnedValidationError
    | ReturnedOtherError String


{-| Initial value for unsubmitted forms
-}
initialSubmissionStatus : SubmissionStatus
initialSubmissionStatus =
    NotSent


{-| Does the submission status indicate an error has occured?
-}
hasSubmissionError : SubmissionStatus -> Bool
hasSubmissionError s =
    case s of
        ReturnedValidationError ->
            True

        ReturnedOtherError _ ->
            True

        _ ->
            False


{-| Are we currently waiting for a response to a submission?
-}
submissionAwaitingResponse : SubmissionStatus -> Bool
submissionAwaitingResponse =
    (==) AwaitingResponse


{-| Show either no notice, a success notice, or an error notice depending on
the SubmissionStatus.
-}
submissionNotice : SubmissionStatus -> String -> Html msg
submissionNotice status successText =
    let
        errorText =
            case status of
                ReturnedValidationError ->
                    "Some errors were found, please correct the items below & try re-submitting the form."

                ReturnedOtherError e ->
                    "An unexpected error occured: " ++ e

                _ ->
                    ""
    in
    if hasSubmissionError status then
        div [ class "notice notice-error" ] [ text errorText ]

    else if status == ReturnedSuccess then
        div [ class "notice notice-success" ]
            [ text successText ]

    else
        text ""


{-| Show a spinner while awaiting a response from a form submission.
-}
submissionSpinner : SubmissionStatus -> Html msg
submissionSpinner status =
    if status == AwaitingResponse then
        div [ class "spinner is-active" ] []

    else
        text ""


statusFromWebData : WebData (Result e a) -> SubmissionStatus
statusFromWebData response =
    case response of
        RemoteData.Success (Ok _) ->
            ReturnedSuccess

        RemoteData.Success (Err _) ->
            ReturnedValidationError

        RemoteData.Failure err ->
            ReturnedOtherError <| toString err

        _ ->
            ReturnedOtherError <| "Unexpected response status: " ++ toString response



-- HTML Helpers


{-| Render a basic form label. Use this for form rows with multiple inputs.
-}
simpleLabel : String -> Html msg
simpleLabel content =
    label [] [ text content ]


{-| Render an input label for a wp-admin form table row.
-}
formLabel : String -> String -> Html msg
formLabel elementId content =
    label [ for elementId ] [ text content ]


{-| Render a form row for a wp-admin form table.
-}
formRow : Html msg -> Html msg -> Html msg
formRow labelElement inputElement =
    tr [] [ th [] [ labelElement ], td [] [ inputElement ] ]



-- HTTP Helpers


{-| Send a GET request to a REST endpoint with the `wp_rest` nonce.
-}
adminGet : String -> { m | wpNonce : String } -> Decoder a -> Cmd (WebData a)
adminGet url model decoder =
    Http.get ("/wp-json/v1/" ++ url ++ "?_wpnonce=" ++ model.wpNonce) decoder
        |> RemoteData.sendRequest


{-| Send a POST request to a REST endpoint with the `wp_rest` nonce.
-}
adminPost : String -> { m | wpNonce : String } -> Value -> Decoder (Result e a) -> Cmd (WebData (Result e a))
adminPost url model body decoder =
    Http.request
        { method = "POST"
        , headers = [ Http.header "X-WP-Nonce" model.wpNonce ]
        , url = "/wp-json/v1/" ++ url
        , body =
            Http.jsonBody body
        , expect =
            Http.expectJson decoder
        , timeout = Nothing
        , withCredentials = False
        }
        |> RemoteData.sendRequest
