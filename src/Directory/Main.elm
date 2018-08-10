module Directory.Main exposing (main)

{-| The Entry Point for the Application.
-}

import Date
import Navigation
import Task
import Directory.Messages exposing (Msg(SetCurrentDate, UrlChange, CommunityPagination))
import Directory.Model exposing (Model, paginationConfig)
import Directory.Routing exposing (Route(..), FilterParam(..), routeParser)
import Directory.Update exposing (update)
import Directory.View exposing (view)


main : Program Never Model Msg
main =
    Navigation.program (routeParser >> UrlChange)
        { init = initialize
        , update = update
        , subscriptions = always Sub.none
        , view = view
        }


initialize : Navigation.Location -> ( Model, Cmd Msg )
initialize location =
    let
        route =
            routeParser location

        ( model, cmd ) =
            Directory.Model.initial route
    in
        ( model
        , Cmd.batch
            [ Task.perform SetCurrentDate Date.now
            , cmd
            ]
        )
