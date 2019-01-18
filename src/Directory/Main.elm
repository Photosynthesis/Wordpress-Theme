module Directory.Main exposing (main)

{-| The Entry Point for the Application.
-}

import Date
import Navigation
import Task
import Directory.Commands exposing (WPNonce(..))
import Directory.Messages exposing (Msg(SetCurrentDate, UrlChange, CommunityPagination, GalleryMsg))
import Directory.Model exposing (Model, paginationConfig)
import Directory.Routing exposing (Route(..), FilterParam(..), routeParser)
import Directory.Update exposing (update)
import Directory.View exposing (view)
import Gallery


main : Program Flags Model Msg
main =
    Navigation.programWithFlags (routeParser >> UrlChange)
        { init = initialize
        , update = update
        , subscriptions = \m -> (Sub.map GalleryMsg <| Gallery.subscriptions m.communityGallery)
        , view = view
        }


type alias Flags =
    { nonce : String
    }


initialize : Flags -> Navigation.Location -> ( Model, Cmd Msg )
initialize { nonce } location =
    let
        route =
            routeParser location

        ( model, cmd ) =
            Directory.Model.initial route (WPNonce nonce)
    in
        ( model
        , Cmd.batch
            [ Task.perform SetCurrentDate Date.now
            , cmd
            ]
        )
