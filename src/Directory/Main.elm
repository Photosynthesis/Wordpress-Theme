module Main exposing (main)

{-| The Entry Point for the Application.
-}

import Date
import Navigation
import Task
import Messages exposing (Msg(SetCurrentDate, UrlChange, CommunityPagination))
import Model exposing (Model, paginationConfig)
import Routing exposing (Route(..), FilterParam(..), routeParser)
import Update exposing (update)
import View exposing (view)


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

        ( model, paginationCmd ) =
            Model.initial route
    in
        ( model
        , Cmd.batch
            [ Task.perform SetCurrentDate Date.now
            , Cmd.map CommunityPagination paginationCmd
            ]
        )
