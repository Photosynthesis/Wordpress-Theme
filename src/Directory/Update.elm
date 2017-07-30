module Update exposing (update)

import Messages exposing (Msg(..))
import Pagination exposing (Pagination)
import Model exposing (Model, paginationConfig)


update : Msg -> Model -> ( Model, Cmd Msg )
update msg model =
    case msg of
        SetCurrentDate currentDate ->
            ( { model | currentDate = Just currentDate }, Cmd.none )

        PreviousPage ->
            let
                ( updatedPagination, paginationCmd ) =
                    Pagination.movePrevious paginationConfig model.communities
            in
                ( { model | communities = updatedPagination }
                , Cmd.map CommunityPagination paginationCmd
                )

        NextPage ->
            let
                ( updatedPagination, paginationCmd ) =
                    Pagination.moveNext paginationConfig model.communities
            in
                ( { model | communities = updatedPagination }
                , Cmd.map CommunityPagination paginationCmd
                )

        JumpToPage page ->
            let
                ( updatedPagination, paginationCmd ) =
                    Pagination.jumpTo paginationConfig model.communities page
            in
                ( { model | communities = updatedPagination }
                , Cmd.map CommunityPagination paginationCmd
                )

        CommunityPagination subMsg ->
            let
                ( paginationModel, paginationCmd ) =
                    Pagination.update subMsg model.communities
            in
                ( { model | communities = paginationModel }
                , Cmd.map CommunityPagination paginationCmd
                )
