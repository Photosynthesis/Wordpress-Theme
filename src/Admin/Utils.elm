module Admin.Utils
    exposing
        ( -- Html Utils
          simpleLabel
        , formLabel
        , formRow
          -- Http Utils
        , adminGet
        , adminPost
        )

{-| Utility Functions for Interacting with the WP Admin Site
-}

import Html exposing (Html, label, text, tr, td, th)
import Html.Attributes exposing (for)
import Http
import Json.Decode exposing (Decoder, Value)
import RemoteData exposing (WebData)


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
