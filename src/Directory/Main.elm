module Directory.Main exposing (main)

{-| The Entry Point for the Application.
-}

import Browser
import Directory.Commands exposing (WPNonce(..))
import Directory.Messages exposing (Msg(..))
import Directory.Model exposing (Model)
import Directory.Ports as Ports
import Directory.Routing exposing (FilterParam(..), Route(..), routeParser)
import Directory.Update exposing (update)
import Directory.View exposing (view)
import Gallery
import Task
import Time


main : Program Flags Model Msg
main =
    Browser.element
        { init = initialize
        , update = update
        , subscriptions =
            \m ->
                Sub.batch
                    [ Sub.map GalleryMsg <| Gallery.subscriptions m.communityGallery
                    , Ports.onUrlChange (routeParser >> UrlChange)
                    ]
        , view = view
        }


type alias Flags =
    { nonce : String
    , location : String
    }


initialize : Flags -> ( Model, Cmd Msg )
initialize { nonce, location } =
    let
        route =
            routeParser location

        ( model, cmd ) =
            Directory.Model.initial route (WPNonce nonce)
    in
    ( model
    , Cmd.batch
        [ Time.now
            |> Task.andThen (\posix -> Task.map (\zone -> ( posix, zone )) Time.here)
            |> Task.perform SetCurrentDate
        , cmd
        ]
    )
