module Pagination
    exposing
        ( Pagination
        , initial
          -- Config
        , Config
        , FetchResponse
        , makeConfig
          -- Retrieving Data
        , getCurrent
        , getPage
        , getTotalPages
        , getTotalItems
        , getError
        , getData
          -- Querying
        , isLoading
        , hasNone
        , hasPrevious
        , hasNext
          -- Modification
        , moveNext
        , movePrevious
        , jumpTo
        , updateData
          -- Update / Messages
        , Msg
        , update
        )

{- For paginating responses

   TODO: Eventually -
    document everything + examples
    fetchRequest per-args cache?
        toString on args to get a (Dict String (Dict PageNumber Chunk))?
    custom page sizes(+ reorganize items when changed)
    publish as separate package
    how to handle filtering/re-ordering/searching?
-}

import Dict exposing (Dict)
import Http
import RemoteData exposing (WebData)


-- Model


{-| A Chunk is a list of items annotated with a page number.
-}
type Chunk a
    = Chunk { items : List a, page : Int }


{-| The `Pagination` type is responsible for storing the fetched items, current
page number, total count and a list of filters being applied to the items.
-}
type Pagination a b
    = Pagination
        { items : Dict Int (WebData (Chunk a))
        , currentPage : Int
        , totalCount : Int
        , requestData : b
        }


{-| The result type of a Pagination Fetch Request. At the minimum, your API
needs to return the items & a total count of all items.
-}
type alias FetchResponse a =
    { items : List a
    , totalCount : Int
    }


{-| The `Config` type is used to build a Fetch Request, given a list of Filters
& a Page Number.
-}
type Config a b
    = Config
        { fetchRequest : b -> Int -> Http.Request (FetchResponse a)
        }


{-| Make a `Config` from a function that takes a list of Filters & a Page Number.
-}
makeConfig : (b -> Int -> Http.Request (FetchResponse a)) -> Config a b
makeConfig fetchRequest =
    Config { fetchRequest = fetchRequest }


{-| Get an initial Pagination & Fetch Commands from a `Config`, list of Filters,
& Page Number.
-}
initial : Config a b -> b -> Int -> ( Pagination a b, Cmd (Msg a) )
initial config requestData page =
    let
        initialModel =
            Pagination
                { items = Dict.empty
                , currentPage = page
                , totalCount = 0
                , requestData = requestData
                }
    in
        ( initialModel
        , getFetches config initialModel
        )


{-| Get the current list of items.
-}
getCurrent : Pagination a b -> List a
getCurrent (Pagination { items, currentPage }) =
    Dict.get currentPage items
        |> Maybe.andThen RemoteData.toMaybe
        |> Maybe.map (\(Chunk { items }) -> items)
        |> Maybe.withDefault []


{-| Get the current page number.
-}
getPage : Pagination a b -> Int
getPage (Pagination { currentPage }) =
    currentPage


{-| Get the total number of pages.
-}
getTotalPages : Pagination a b -> Int
getTotalPages (Pagination { totalCount }) =
    ceiling <| toFloat totalCount / toFloat 15


{-| Get the total item count.
-}
getTotalItems : Pagination a b -> Int
getTotalItems (Pagination { totalCount }) =
    totalCount


{-| Return the current page's fetch request's error if it has one.
-}
getError : Pagination a b -> Maybe Http.Error
getError (Pagination { items, currentPage }) =
    case Dict.get currentPage items of
        Just (RemoteData.Failure e) ->
            Just e

        _ ->
            Nothing


{-| Return the Extra Request Data for the current Pagination.
-}
getData : Pagination a b -> b
getData (Pagination { requestData }) =
    requestData


{-| Does the current page have no items? This will only be true if the page was
fetched successfully but returned no items.
-}
hasNone : Pagination a b -> Bool
hasNone (Pagination { items, currentPage }) =
    case Dict.get currentPage items of
        Just (RemoteData.Success chunk) ->
            getChunkItems chunk
                |> List.isEmpty

        _ ->
            False


{-| Is the current page's fetch request still loading?
-}
isLoading : Pagination a b -> Bool
isLoading (Pagination { items, currentPage }) =
    case Dict.get currentPage items of
        Just RemoteData.Loading ->
            True

        Just _ ->
            False

        _ ->
            True


{-| Are there page's before the current one?
-}
hasPrevious : Pagination a b -> Bool
hasPrevious (Pagination { currentPage }) =
    currentPage /= 1


{-| Are there page's after the current one?
-}
hasNext : Pagination a b -> Bool
hasNext ((Pagination { currentPage }) as pagination) =
    currentPage /= getTotalPages pagination


{-| Move to the next page.
TODO: re-implement as call to `jumpTo`?
-}
moveNext : Config a b -> Pagination a b -> ( Pagination a b, Cmd (Msg a) )
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


{-| Move to the previous page.
TODO: re-implement as call to `jumpTo`?
-}
movePrevious : Config a b -> Pagination a b -> ( Pagination a b, Cmd (Msg a) )
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


{-| Move to a specific page.
-}
jumpTo : Config a b -> Pagination a b -> Int -> ( Pagination a b, Cmd (Msg a) )
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


{-| Replace the current Extra Request Data, jumping to page 1 & performing new
fetch requests. Does nothing if the Data is equal to existing Data.
-}
updateData : Config a b -> Pagination a b -> b -> ( Pagination a b, Cmd (Msg a) )
updateData config ((Pagination pagination) as model) newData =
    if newData == pagination.requestData then
        ( model, Cmd.none )
    else
        initial config newData 1



-- Update


type Msg a
    = FetchPage Int (WebData (FetchResponse a))


update : Msg a -> Pagination a b -> ( Pagination a b, Cmd (Msg a) )
update msg (Pagination model) =
    case msg of
        FetchPage page ((RemoteData.Failure e) as data) ->
            let
                _ =
                    Debug.log "Fetch Error: "
                        data
            in
                ( Pagination
                    { model
                        | items = Dict.insert page (RemoteData.Failure e) model.items
                    }
                , Cmd.none
                )

        FetchPage page (RemoteData.Success { items, totalCount }) ->
            let
                newChunk =
                    Chunk { items = items, page = page }

                updatedModel =
                    Pagination
                        { model
                            | totalCount = totalCount
                            , items = Dict.insert page (RemoteData.succeed newChunk) model.items
                        }
            in
                ( updatedModel, Cmd.none )

        FetchPage page data ->
            let
                newData =
                    RemoteData.map (\{ items } -> Chunk { items = items, page = page })
                        data
            in
                ( Pagination { model | items = Dict.insert page newData model.items }
                , Cmd.none
                )



-- Utils


{-| Get the items for a `Chunk`
-}
getChunkItems : Chunk a -> List a
getChunkItems (Chunk { items }) =
    items


{-| Return the Fetch commands for the current page. This will also prefetch the
previous/next pages if they exist.
-}
getFetches : Config a b -> Pagination a b -> Cmd (Msg a)
getFetches (Config config) (Pagination pagination) =
    let
        currentPage =
            getPage (Pagination pagination)

        totalPages =
            getTotalPages (Pagination pagination)

        hasItems offset =
            Dict.get (pagination.currentPage + offset) pagination.items
                |> Maybe.andThen RemoteData.toMaybe
                |> Maybe.map (not << List.isEmpty << getChunkItems)
                |> Maybe.withDefault False

        currentFetch =
            if not <| hasItems 0 then
                config.fetchRequest pagination.requestData currentPage
                    |> RemoteData.sendRequest
                    |> Cmd.map (FetchPage currentPage)
            else
                Cmd.none

        previousFetch =
            if not <| hasItems -1 then
                config.fetchRequest pagination.requestData (currentPage - 1)
                    |> RemoteData.sendRequest
                    |> Cmd.map (FetchPage <| currentPage - 1)
            else
                Cmd.none

        nextFetch =
            if not <| hasItems 1 then
                config.fetchRequest pagination.requestData (currentPage + 1)
                    |> RemoteData.sendRequest
                    |> Cmd.map (FetchPage <| currentPage + 1)
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
