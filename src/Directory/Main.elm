module Main exposing (main)

import Date
import Navigation
import Task
import Messages exposing (Msg(SetCurrentDate, UrlChange, CommunityPagination))
import Model exposing (Model, paginationConfig)
import Routing exposing (Route(..), routeParser)
import Update exposing (update)
import View exposing (view)


-- TODO: Docstrings for everything


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
            case route of
                Listings page ->
                    Model.initial page
    in
        ( model
        , Cmd.batch
            [ Task.perform SetCurrentDate Date.now
            , Cmd.map CommunityPagination paginationCmd
            ]
        )
