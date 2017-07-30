module Pagination
    exposing
        ( Pagination
        , initial
          -- Retrieving Data
        , getCurrent
        , getPage
        , getTotalPages
        , getTotalItems
          -- Querying
        , hasNone
        , hasPrevious
        , hasNext
          -- Modification
        , moveNext
        , movePrevious
        , jumpTo
          -- Config
        , Config
        , makeConfig
          -- Update / Messages
        , Msg
        , FetchResponse
        , update
        )

{- For paginating responses

   TODO: Eventually -
    document everything
    custom page sizes(+ reorganize items when changed)
    publish as separate package
    how to handle filtering/re-ordering/searching?
-}

import Dict exposing (Dict)
import Http


-- Model


type Chunk a
    = Chunk { items : List a, page : Int }


type Pagination a
    = Pagination
        { items : Dict Int (Chunk a)
        , currentPage : Int
        , totalCount : Int
        }


type alias FetchResponse a =
    { items : List a
    , totalCount : Int
    }


type Config a
    = Config
        { fetchRequest : Int -> Http.Request (FetchResponse a)
        }


makeConfig : (Int -> Http.Request (FetchResponse a)) -> Config a
makeConfig fetchRequest =
    Config { fetchRequest = fetchRequest }


initial : Config a -> Int -> ( Pagination a, Cmd (Msg a) )
initial config page =
    let
        initialModel =
            Pagination
                { items = Dict.empty
                , currentPage = page
                , totalCount = 0
                }
    in
        ( initialModel
        , getFetches config initialModel
        )


getCurrent : Pagination a -> List a
getCurrent (Pagination { items, currentPage }) =
    Dict.get currentPage items
        |> Maybe.map (\(Chunk { items }) -> items)
        |> Maybe.withDefault []


getPage : Pagination a -> Int
getPage (Pagination { currentPage }) =
    currentPage


getTotalPages : Pagination a -> Int
getTotalPages (Pagination { totalCount }) =
    ceiling <| toFloat totalCount / toFloat 15


getTotalItems : Pagination a -> Int
getTotalItems (Pagination { totalCount }) =
    totalCount


hasNone : Pagination a -> Bool
hasNone (Pagination { items, currentPage }) =
    Dict.get currentPage items
        |> Maybe.map (getChunkItems >> List.isEmpty)
        |> Maybe.withDefault True


hasPrevious : Pagination a -> Bool
hasPrevious (Pagination { currentPage }) =
    currentPage /= 1


hasNext : Pagination a -> Bool
hasNext ((Pagination { currentPage }) as pagination) =
    currentPage /= getTotalPages pagination


moveNext : Config a -> Pagination a -> ( Pagination a, Cmd (Msg a) )
moveNext (Config config) ((Pagination pagination) as model) =
    let
        currentPage =
            getPage (Pagination pagination)

        updatedModel =
            Dict.get (currentPage + 1) pagination.items
                |> Maybe.map
                    (\_ -> Pagination { pagination | currentPage = currentPage + 1 })
                |> Maybe.withDefault model
    in
        ( updatedModel, getFetches (Config config) updatedModel )


movePrevious : Config a -> Pagination a -> ( Pagination a, Cmd (Msg a) )
movePrevious (Config config) ((Pagination pagination) as model) =
    let
        currentPage =
            getPage (Pagination pagination)

        updatedModel =
            Dict.get (currentPage - 1) pagination.items
                |> Maybe.map
                    (\_ -> Pagination { pagination | currentPage = currentPage - 1 })
                |> Maybe.withDefault model
    in
        ( updatedModel, getFetches (Config config) updatedModel )


jumpTo : Config a -> Pagination a -> Int -> ( Pagination a, Cmd (Msg a) )
jumpTo (Config config) ((Pagination pagination) as model) page =
    let
        canJump =
            page > 0 && page <= getTotalPages (Pagination pagination)

        jumpDifference =
            page - getPage (Pagination pagination)

        updatedModel =
            if canJump then
                Pagination { pagination | currentPage = page }
            else
                model
    in
        ( updatedModel, getFetches (Config config) updatedModel )



-- Update


type Msg a
    = FetchPage Int (Result Http.Error (FetchResponse a))


update : Msg a -> Pagination a -> ( Pagination a, Cmd (Msg a) )
update msg (Pagination model) =
    case msg of
        FetchPage _ (Err e) ->
            let
                _ =
                    Debug.log "Fetch Error: " e
            in
                ( Pagination model, Cmd.none )

        FetchPage page (Ok { items, totalCount }) ->
            let
                newChunk =
                    Chunk { items = items, page = page }

                updatedModel =
                    Pagination
                        { model
                            | totalCount = totalCount
                            , items = Dict.insert page newChunk model.items
                        }
            in
                ( updatedModel, Cmd.none )


getChunkItems : Chunk a -> List a
getChunkItems (Chunk { items }) =
    items


getFetches : Config a -> Pagination a -> Cmd (Msg a)
getFetches (Config config) (Pagination pagination) =
    let
        currentPage =
            getPage (Pagination pagination)

        totalPages =
            getTotalPages (Pagination pagination)

        hasItems offset =
            Dict.get (pagination.currentPage + offset) pagination.items
                |> Maybe.map (not << List.isEmpty << getChunkItems)
                |> Maybe.withDefault False

        currentFetch =
            if not <| hasItems 0 then
                config.fetchRequest currentPage
                    |> Http.send (FetchPage currentPage)
            else
                Cmd.none

        previousFetch =
            if not <| hasItems -1 then
                config.fetchRequest (currentPage - 1)
                    |> Http.send (FetchPage <| currentPage - 1)
            else
                Cmd.none

        nextFetch =
            if not <| hasItems 1 then
                config.fetchRequest (currentPage + 1)
                    |> Http.send (FetchPage <| currentPage + 1)
            else
                Cmd.none
    in
        if currentPage > 1 && (currentPage < totalPages || totalPages == 0) then
            Cmd.batch [ currentFetch, previousFetch, nextFetch ]
        else if currentPage > 1 then
            Cmd.batch [ currentFetch, previousFetch ]
        else if (currentPage < totalPages || totalPages == 0) then
            Cmd.batch [ currentFetch, nextFetch ]
        else
            currentFetch
