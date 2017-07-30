module Main exposing (main)

import Date
import Html
import Task
import Messages exposing (Msg(SetCurrentDate, CommunityPagination))
import Model exposing (Model, paginationConfig)
import Update exposing (update)
import View exposing (view)


-- TODO: Docstrings for everything


main : Program Never Model Msg
main =
    Html.program
        { init = initialize
        , update = update
        , subscriptions = always Sub.none
        , view = view
        }


initialize : ( Model, Cmd Msg )
initialize =
    let
        ( model, paginationCmd ) =
            Model.initial 1
    in
        ( model
        , Cmd.batch
            [ Task.perform SetCurrentDate Date.now
            , Cmd.map CommunityPagination paginationCmd
            ]
        )
